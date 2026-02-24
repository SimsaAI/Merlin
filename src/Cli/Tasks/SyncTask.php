<?php

namespace Merlin\Cli\Tasks;

use Merlin\AppContext;
use Merlin\Cli\Task;
use Merlin\Sync\SyncOptions;
use Merlin\Sync\SyncResult;
use Merlin\Sync\SyncRunner;

/**
 * CLI task for synchronising PHP model properties from the database schema (DB→PHP)
 * and for scaffolding new model files from database tables.
 *
 * Usage:
 *   php console.php sync all   <models-dir> [--apply] [--database=<role>]
 *                              [--generate-accessors] [--field-visibility=<public|protected|private>]
 *                              [--no-deprecate] [--create-missing] [--namespace=<ns>]
 *   php console.php sync model <file>        [--apply] [--database=<role>]
 *                              [--generate-accessors] [--field-visibility=<public|protected|private>]
 *                              [--no-deprecate]
 *   php console.php sync make  <ClassName>   <directory> [--apply] [--database=<role>]
 *                              [--namespace=<ns>] [--generate-accessors]
 *                              [--field-visibility=<public|protected|private>] [--no-deprecate]
 *
 * By default the task runs in **dry-run** mode and only reports changes.
 * Pass --apply to write the updated model files to disk.
 *
 * Examples:
 *   php console.php sync all  src/Models                                  # dry-run
 *   php console.php sync all  src/Models --apply                          # apply
 *   php console.php sync all  src/Models --apply --generate-accessors     # with accessors
 *   php console.php sync all  src/Models --apply --field-visibility=protected
 *   php console.php sync all  src/Models --apply --no-deprecate
 *   php console.php sync all  src/Models --apply --create-missing --namespace=App\\Models
 *   php console.php sync model src/Models/User.php --apply
 *   php console.php sync make  User src/Models --namespace=App\\Models --apply
 */
class SyncTask extends Task
{
    // -------------------------------------------------------------------------
    //  Actions
    // -------------------------------------------------------------------------

    /**
     * Scan a directory recursively, find all PHP files that extend Model,
     * and sync each one against the database.
     *
     * @param string $dir Directory to scan (required)
     */
    public function allAction(string $dir = ''): void
    {
        if ($dir === '') {
            $this->error("Usage: sync all <models-directory> [--apply] [--database=<role>] [--generate-accessors] [--field-visibility=<vis>] [--no-deprecate] [--create-missing] [--namespace=<ns>]");
            return;
        }

        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return;
        }

        $dryRun = !isset($this->options['apply']);
        $dbRole = $this->options['database'] ?? 'read';
        $options = $this->buildOptions();
        $createMissing = isset($this->options['create-missing']);
        $files = $this->findModelFiles($dir);
        $runner = $this->buildRunner();

        if ($createMissing) {
            $namespace = ($this->options['namespace'] ?? '') ?: $this->detectNamespace($dir);

            try {
                $allTables = $runner->listDatabaseTables($dbRole);
            } catch (\Throwable $e) {
                $this->error("Could not list database tables: {$e->getMessage()}");
                return;
            }

            // Collect which tables are already covered by existing model files.
            $coveredTables = [];
            foreach ($files as $file) {
                $table = $runner->getModelTableName($file);
                if ($table !== null) {
                    $coveredTables[$table] = true;
                }
            }

            foreach ($allTables as $table) {
                if (isset($coveredTables[$table])) {
                    continue;
                }

                $className = $this->classNameFromTable($table);
                $filePath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $className . '.php';

                if (file_exists($filePath)) {
                    continue; // never overwrite an existing file
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] Would create: {$filePath} (table: {$table})");
                } elseif ($namespace !== '') {
                    $runner->createModelFile($filePath, $namespace, $className, $table);
                    $this->line("Created: {$filePath} (table: {$table})");
                    $files[] = $filePath;
                } else {
                    $this->error("Cannot create model for table '{$table}': namespace unknown. Pass --namespace=<ns>.");
                }
            }
        }

        if (empty($files)) {
            $this->line("No PHP files found in {$dir}");
            return;
        }

        $this->line(
            $dryRun
            ? "Dry-run: scanning " . count($files) . " file(s) in {$dir} …"
            : "Applying: syncing " . count($files) . " file(s) in {$dir} …"
        );
        $this->line('');

        $results = $runner->syncAll($files, $dryRun, $dbRole, $options);

        $this->printResults($results);
    }

    /**
     * Sync a single model file against the database.
     *
     * @param string $file Path to the PHP model file (required)
     */
    public function modelAction(string $file = ''): void
    {
        if ($file === '') {
            $this->error("Usage: sync model <file> [--apply] [--database=<role>] [--generate-accessors] [--field-visibility=<vis>] [--no-deprecate]");
            return;
        }

        $realPath = realpath($file);
        if ($realPath === false || !is_file($realPath)) {
            $this->error("File not found: {$file}");
            return;
        }

        $dryRun = !isset($this->options['apply']);
        $dbRole = $this->options['database'] ?? 'read';
        $options = $this->buildOptions();

        $this->line(
            $dryRun
            ? "Dry-run: {$realPath}"
            : "Applying: {$realPath}"
        );
        $this->line('');

        $runner = $this->buildRunner();
        $result = $runner->syncModel($realPath, $dryRun, $dbRole, $options);

        $this->printResults([$result]);
    }

    /**
     * Scaffold a new model class from a database table and immediately sync its properties.
     *
     * @param string $className Short class name without namespace (e.g. User)
     * @param string $dir       Target directory for the new file
     */
    public function makeAction(string $className = '', string $dir = ''): void
    {
        if ($className === '' || $dir === '') {
            $this->error("Usage: sync make <ClassName> <directory> [--namespace=<ns>] [--apply] [--database=<role>]");
            return;
        }

        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return;
        }

        $dryRun = !isset($this->options['apply']);
        $dbRole = $this->options['database'] ?? 'read';
        $namespace = ($this->options['namespace'] ?? '') ?: $this->detectNamespace($dir);
        $options = $this->buildOptions();

        $tableName = $this->deriveTableName($className);
        $filePath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            $this->error("File already exists: {$filePath}");
            return;
        }

        if ($namespace === '') {
            $this->error("Could not detect namespace. Please pass --namespace=<ns>");
            return;
        }

        if ($dryRun) {
            $this->line("[DRY-RUN] Would create: {$filePath}");
            $this->line("          Namespace:  {$namespace}");
            $this->line("          Class:      {$className}");
            $this->line("          Table:      {$tableName}");
            return;
        }

        $runner = $this->buildRunner();
        $runner->createModelFile($filePath, $namespace, $className, $tableName);
        $this->line("Created: {$filePath}");
        $this->line('');

        $result = $runner->syncModel($filePath, false, $dbRole, $options);
        $this->printResults([$result]);
    }

    // -------------------------------------------------------------------------
    //  Internals
    // -------------------------------------------------------------------------

    private function buildRunner(): SyncRunner
    {
        return new SyncRunner(AppContext::instance()->dbManager());
    }

    /** @return string[] Absolute paths to PHP files inside $dir */
    private function findModelFiles(string $dir): array
    {
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iter as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        sort($files);
        return $files;
    }

    private function buildOptions(): SyncOptions
    {
        return new SyncOptions(
            generateAccessors: isset($this->options['generate-accessors']),
            fieldVisibility: $this->options['field-visibility'] ?? 'public',
            deprecate: !isset($this->options['deprecate']) || $this->options['deprecate'],
        );
    }

    /** Detect the PHP namespace from any .php file directly inside $dir. */
    private function detectNamespace(string $dir): string
    {
        foreach (glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $code = file_get_contents($file);
            if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $code, $m)) {
                return $m[1];
            }
        }
        return '';
    }

    /** Convert a snake_case table name to a PascalCase class name (no singularisation). */
    private function classNameFromTable(string $table): string
    {
        return str_replace('_', '', ucwords($table, '_'));
    }

    /** Convert a PascalCase class name to the snake_case table name used by default. */
    private function deriveTableName(string $className): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }
    /** @param SyncResult[] $results */
    private function printResults(array $results): void
    {
        $totalChanges = 0;
        $totalErrors = 0;

        foreach ($results as $result) {
            if ($result->isSuccess() && $result->hasChanges()) {
                $this->warn($result->summary());
                foreach ($result->operations as $op) {
                    $this->line('    ' . $this->style('•', 'yellow') . ' ' . $this->describeOp($op));
                }
                $totalChanges++;
            } elseif (!$result->isSuccess()) {
                $this->error($result->summary());
                $totalErrors++;
            } else {
                $this->muted($result->summary());
            }
        }

        $this->writeln();
        $summary = sprintf('Done. %d model(s) with changes, %d error(s).', $totalChanges, $totalErrors);
        if ($totalErrors > 0) {
            $this->error($summary);
        } elseif ($totalChanges > 0) {
            $this->warn($summary);
        } else {
            $this->success($summary);
        }
    }

    private function describeOp(object $op): string
    {
        return match (true) {
            $op instanceof \Merlin\Sync\AddProperty =>
            "add    \${$op->property}: {$op->type}"
            . ($op->comment ? " — {$op->comment}" : ''),
            $op instanceof \Merlin\Sync\AddAccessor =>
            "accessor {$op->methodName}() for \${$op->property}",
            $op instanceof \Merlin\Sync\RemoveProperty =>
            "deprecate \${$op->property}",
            $op instanceof \Merlin\Sync\UpdatePropertyType =>
            "retype \${$op->property}: {$op->oldType} → {$op->newType}",
            $op instanceof \Merlin\Sync\UpdatePropertyComment =>
            "comment \${$op->property}",
            $op instanceof \Merlin\Sync\UpdateClassComment =>
            "class comment",
            default => get_class($op),
        };
    }

}
