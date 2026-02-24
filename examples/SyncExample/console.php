#!/usr/bin/env php
<?php
/**
 * SyncExample CLI entry point.
 *
 * Usage:
 *   php console.php <task> <action> [args...]
 *
 * Sync examples:
 *   php console.php sync all  Models                                          # dry-run
 *   php console.php sync all  Models --apply                                  # apply
 *   php console.php sync all  Models --apply --generate-accessors             # with getters/setters
 *   php console.php sync all  Models --apply --field-visibility=protected     # protected fields
 *   php console.php sync all  Models --apply --no-deprecate                   # skip @deprecated tags
 *   php console.php sync all  Models --apply --create-missing \
 *       --namespace=SyncExample\\Models                                        # scaffold new models
 *   php console.php sync model Models/User.php --apply
 *   php console.php sync model Models/User.php --apply --generate-accessors
 *   php console.php sync make Order Models --namespace=SyncExample\\Models --apply
 */

chdir(__DIR__);

require __DIR__ . '/bootstrap.php';

use Merlin\Cli\Console;

$console = new Console();

$console->process(
    $argv[1] ?? null,
    $argv[2] ?? null,
    array_slice($argv, 3)
);
