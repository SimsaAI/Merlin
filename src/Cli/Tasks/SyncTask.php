<?php

namespace Merlin\Cli\Tasks;

use Merlin\AppContext;
use Merlin\Cli\Task;
use Merlin\Sync\SyncResult;
use Merlin\Sync\SyncRunner;

/**
 * CLI task for synchronising PHP model properties from the database schema (DB→PHP).
 *
 * Usage:
 *   php console.php sync all  <models-dir> [--apply] [--database=<role>]
 *   php console.php sync model <file-or-glob> [--apply] [--database=<role>]
 *
 * By default the task runs in **dry-run** mode and only reports changes.
 * Pass --apply to write the updated model files to disk.
 *
 * Examples:
 *   php console.php sync all  src/Models          # dry-run all models
 *   php console.php sync all  src/Models --apply  # apply to all models
 *   php console.php sync model src/Models/User.php --apply
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
     * @param string $dir     Directory to scan (required)
     * @param string ...$args Optional flags: --apply, --database=<role>
     */
    public function allAction(string $dir = '', string ...$args): void
    {
        if ($dir === '') {
            $this->error("Usage: sync all <models-directory> [--apply] [--database=<role>]");
            return;
        }

        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return;
        }

        $dryRun = !in_array('--apply', $args, true);
        $dbRole = $this->extractDatabaseFlag($args);
        $files = $this->findModelFiles($dir);

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

        $runner = $this->buildRunner();
        $results = $runner->syncAll($files, $dryRun, $dbRole);

        $this->printResults($results);
    }

    /**
     * Sync a single model file against the database.
     *
     * @param string $file    Path to the PHP model file (required)
     * @param string ...$args Optional flags: --apply, --database=<role>
     */
    public function modelAction(string $file = '', string ...$args): void
    {
        if ($file === '') {
            $this->error("Usage: sync model <file> [--apply] [--database=<role>]");
            return;
        }

        $realPath = realpath($file);
        if ($realPath === false || !is_file($realPath)) {
            $this->error("File not found: {$file}");
            return;
        }

        $dryRun = !in_array('--apply', $args, true);
        $dbRole = $this->extractDatabaseFlag($args);

        $this->line(
            $dryRun
            ? "Dry-run: {$realPath}"
            : "Applying: {$realPath}"
        );
        $this->line('');

        $runner = $this->buildRunner();
        $result = $runner->syncModel($realPath, $dryRun, $dbRole);

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

    /** Extract --database=<role> from args, default 'read' */
    private function extractDatabaseFlag(array $args): string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--database=')) {
                return substr($arg, strlen('--database='));
            }
        }
        return 'read';
    }

    /** @param SyncResult[] $results */
    private function printResults(array $results): void
    {
        $totalChanges = 0;
        $totalErrors = 0;

        foreach ($results as $result) {
            $this->line($result->summary());

            if ($result->isSuccess() && $result->hasChanges()) {
                foreach ($result->operations as $op) {
                    $this->line('    • ' . $this->describeOp($op));
                }
                $totalChanges++;
            } elseif (!$result->isSuccess()) {
                $totalErrors++;
            }
        }

        $this->line('');
        $this->line(sprintf(
            'Done. %d model(s) with changes, %d error(s).',
            $totalChanges,
            $totalErrors
        ));
    }

    private function describeOp(object $op): string
    {
        return match (true) {
            $op instanceof \Merlin\Sync\AddProperty =>
            "add    \${$op->property}: {$op->type}"
            . ($op->comment ? " — {$op->comment}" : ''),
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

    private function line(string $text): void
    {
        echo $text . PHP_EOL;
    }

    private function error(string $text): void
    {
        echo '[ERROR] ' . $text . PHP_EOL;
    }
}
