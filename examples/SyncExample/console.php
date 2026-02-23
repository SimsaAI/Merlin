#!/usr/bin/env php
<?php
/**
 * SyncExample CLI entry point.
 *
 * Usage:
 *   php console.php <task> <action> [args...]
 *
 * Sync examples:
 *   php console.php sync all  Models --dry-run
 *   php console.php sync all  Models --apply
 *   php console.php sync model Models/User.php --apply
 *   php console.php sync model Models/User.php --apply --database=read
 */

declare(strict_types=1);

use Merlin\Cli\Exception as CliException;

chdir(__DIR__);

require __DIR__ . '/bootstrap.php';

use Merlin\Cli\Console;

$console = new Console();

// Point the task resolver to the framework's built-in tasks namespace.
// This makes the 'sync' task resolve to \Merlin\Cli\Tasks\SyncTask.
$console->setNamespace('Merlin\\Cli\\Tasks');

try {
    $console->process(
        $argv[1] ?? null,
        $argv[2] ?? null,
        array_slice($argv, 3)
    );
} catch (CliException) {
    $script = basename($argv[0]);
    echo <<<"EOT"
Usage:
   php $script sync all Models --dry-run
   php $script sync all Models --apply
   php $script sync model Models/User.php --apply
   php $script sync model Models/User.php --apply --database=read


EOT;
}
