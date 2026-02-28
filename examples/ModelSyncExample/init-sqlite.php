<?php
// Quick helper to initialise the SQLite test database.
// Run: php init-sqlite.php

chdir(__DIR__);

$dbFile = __DIR__ . '/sync_example.sqlite';

if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "Removed existing database.\n";
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents(__DIR__ . '/sql/sqlite.sql');

// Split on semicolons and execute each statement
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if ($stmt !== '') {
        $pdo->exec($stmt);
    }
}

echo "SQLite database initialised at: {$dbFile}\n";
echo "Tables: " . implode(', ', $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
