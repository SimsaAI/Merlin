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
 *   model-sync all   [<models-dir>] [--apply] [--database=<role>]
 *                                 [--generate-accessors] [--no-deprecate]
 *                                 [--field-visibility=<public|protected|private>]
 *                                 [--create-missing] [--namespace=<ns>]
 *   model-sync model <file-or-class> [--apply] [--database=<role>]
 *                                 [--generate-accessors]
 *                                 [--field-visibility=<public|protected|private>]
 *                                 [--no-deprecate] [--directory=<dir>]
 *   model-sync make  <ClassName>  [<directory>] [--apply]
 *                                 [--database=<role>] [--namespace=<ns>]
 *                                 [--generate-accessors] [--no-deprecate]
 *                                 [--field-visibility=<public|protected|private>]
 *
 * The <file-or-class> argument for `model-sync model` accepts:
 *   - A file path:               src/Models/User.php
 *   - A short class name:        User          (discovered via PSR-4 / --directory)
 *   - A fully-qualified name:    App\Models\User
 *
 * By default the task only reports changes.
 * Pass --apply to write the updated model files to disk.
 *
 * Options:
 *   --apply                     Apply changes to files instead of just
 *                               reporting them
 *   --create-missing            Create new model files for tables that don't
 *                               have a corresponding model yet
 *   --database=<role>           Database role to use for schema introspection
 *                               (default: "read")
 *   --directory=<dir>           Hint directory for class-name resolution in
 *                               `model-sync model` (optional)
 *   --field-visibility=<vis>    Visibility for generated properties (default:
 *                               "public")
 *   --generate-accessors        Also generate getter/setter methods for each
 *                               property
 *   --namespace=<ns>            Namespace to use when creating new model files
 *                               (required if --create-missing is used)
 *   --no-deprecate              Don't add @deprecated tags to removed
 *                               properties
 *
 * Examples:
 *   php console.php model-sync all                                          # auto-discover App\Models
 *   php console.php model-sync all  src/Models                              # dry-run
 *   php console.php model-sync all  src/Models --apply                      # apply
 *   php console.php model-sync all  src/Models --apply --generate-accessors # with accessors
 *   php console.php model-sync all  src/Models --apply --field-visibility=protected
 *   php console.php model-sync all  src/Models --apply --no-deprecate
 *   php console.php model-sync all  src/Models --apply --create-missing --namespace=App\\Models
 *   php console.php model-sync model src/Models/User.php --apply            # file path
 *   php console.php model-sync model User --apply                           # short class name (PSR-4)
 *   php console.php model-sync model App\\Models\\User --apply              # fully-qualified name
 *   php console.php model-sync model User --directory=src/Models --apply    # with directory hint
 *   php console.php model-sync make  User                                   # auto-discover App\Models dir
 *   php console.php model-sync make  User src/Models --namespace=App\\Models --apply
 */
class ModelSyncTask extends Task
{
    // -------------------------------------------------------------------------
    //  Actions
    // -------------------------------------------------------------------------

    /**
     * Scan a directory recursively, find all PHP files that extend Model,
     * and sync each one against the database.
     *
     * @param string $dir Directory to scan (optional – defaults to App\\Models via PSR-4)
     */
    public function allAction(string $dir = ''): void
    {
        if ($dir === '') {
            $dir = $this->resolveModelsDir();
            if ($dir === null) {
                $this->error("No models directory found. Pass a directory or configure App\\Models in composer.json PSR-4.");
                return;
            }
            $this->muted("Auto-discovered models directory: {$dir}");
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
     * Sync a single model against the database.
     *
     * The argument may be a file path, a short class name, or a fully-qualified
     * class name. Short and qualified class names are resolved to file paths
     * via the PSR-4 autoloading map. Use --directory=<dir> to narrow the search
     * when two classes share the same short name.
     *
     * @param string $file File path, short class name, or fully-qualified class name (required)
     */
    public function modelAction(string $file = ''): void
    {
        if ($file === '') {
            $this->error("Usage: model-sync model <file-or-class> [--apply] [--database=<role>] [--generate-accessors] [--field-visibility=<vis>] [--no-deprecate] [--directory=<dir>]");
            return;
        }

        // Try to resolve as a literal file path first (preserves existing behaviour).
        $realPath = realpath($file);

        if ($realPath === false || !is_file($realPath)) {
            // Fall back to class-name resolution (short name or FQN).
            $dir = isset($this->options['directory']) ? $this->options['directory'] : null;
            $realPath = $this->findModelFileByClassName($file, $dir);

            if ($realPath === null) {
                $this->error("Cannot resolve '{$file}' to a model file. Pass a valid file path, a short class name (e.g. User), or a fully-qualified name (e.g. App\\Models\\User). Use --directory=<dir> to narrow the search.");
                return;
            }

            $this->muted("Resolved '{$file}' → {$realPath}");
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
     * @param string $dir       Target directory for the new file (optional – defaults to App\\Models via PSR-4)
     */
    public function makeAction(string $className = '', string $dir = ''): void
    {
        if ($className === '') {
            $this->error("Usage: model-sync make <ClassName> [<directory>] [--namespace=<ns>] [--apply] [--database=<role>]");
            return;
        }

        if ($dir === '') {
            $dir = $this->resolveModelsDir();
            if ($dir === null) {
                $this->error("No models directory found. Pass a directory or configure App\\Models in composer.json PSR-4.");
                return;
            }
            $this->muted("Auto-discovered models directory: {$dir}");
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
        return $this->console->scanDirectory($dir);
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
        return $this->console->detectNamespace($dir);
    }

    /**
     * Resolve the default models directory.
     *
     * Tries to find App\Models via the PSR-4 map; falls back to checking for
     * an app/Models or src/Models directory relative to the working directory.
     */
    private function resolveModelsDir(): ?string
    {
        $path = $this->console->resolvePsr4Path('App\\Models');
        if ($path !== null) {
            return $path;
        }
        $cwd = getcwd();
        foreach (['app/Models', 'Models', 'src/Models', 'App/Models'] as $rel) {
            $abs = $cwd . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_dir($abs)) {
                return realpath($abs);
            }
        }
        return null;
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
                $this->info($result->summary());
                foreach ($result->operations as $op) {
                    $this->line('    ' . $this->style('•', 'bmagenta') . ' ' . $this->describeOp($op));
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
            $this->info($summary);
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

    /**
     * Attempt to resolve a PHP class name (short or fully-qualified) to an
     * absolute file path using the PSR-4 autoloading map.
     *
     * Short name (e.g. "User"):
     *   1. Check $baseDir/ClassName.php if $baseDir is provided.
     *   2. Scan each PSR-4 root directory recursively for ClassName.php files
     *      whose declared class name matches (to avoid false positives).
     *
     * Fully-qualified name (e.g. "App\Models\User"):
     *   Derive the path from the PSR-4 prefix map (namespace → directory,
     *   then append remaining segments + ".php").
     *
     * Returns null if the file cannot be found.
     */
    protected function findModelFileByClassName(string $className, ?string $baseDir = null): ?string
    {
        $isQualified = str_contains($className, '\\');

        if ($isQualified) {
            $map = $this->console->readComposerPsr4();
            $nsClean = ltrim($className, '\\');
            $bestPrefix = null;
            $bestDir = null;
            foreach ($map as $prefix => $dir) {
                $prefixClean = rtrim($prefix, '\\');
                if ($nsClean === $prefixClean || str_starts_with($nsClean, $prefixClean . '\\')) {
                    if ($bestPrefix === null || strlen($prefixClean) > strlen($bestPrefix)) {
                        $bestPrefix = $prefixClean;
                        $bestDir = $dir;
                    }
                }
            }
            if ($bestPrefix !== null) {
                $suffix = ltrim(substr($nsClean, strlen($bestPrefix)), '\\');
                $relative = str_replace('\\', DIRECTORY_SEPARATOR, $suffix) . '.php';
                $path = $bestDir . DIRECTORY_SEPARATOR . $relative;
                $real = realpath($path);
                return ($real !== false && is_file($real)) ? $real : null;
            }
            return null;
        }

        // Short name: check the explicit base directory first.
        if ($baseDir !== null) {
            $candidate = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $className . '.php';
            $real = realpath($candidate);
            if ($real !== false && is_file($real)) {
                return $real;
            }
        }

        // Scan PSR-4 roots recursively for a file named ClassName.php whose
        // declared class name actually matches (avoids same-named file false-positives).
        foreach ($this->console->readComposerPsr4() as $dir) {
            foreach ($this->console->scanDirectory($dir) as $file) {
                if (basename($file, '.php') === $className) {
                    $fqn = $this->console->extractClassFromFile($file);
                    if ($fqn !== null && substr($fqn, strrpos($fqn, '\\') + 1) === $className) {
                        return $file;
                    }
                }
            }
        }

        return null;
    }

}
