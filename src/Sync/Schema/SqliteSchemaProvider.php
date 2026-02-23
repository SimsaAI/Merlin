<?php
namespace Merlin\Sync\Schema;

use PDO;

class SqliteSchemaProvider implements SchemaProvider
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listTables(): array
    {
        $stmt = $this->pdo->query("
            SELECT name FROM sqlite_master WHERE type='table'
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTableSchema(string $table): TableSchema
    {
        return new TableSchema(
            $table,
            null, // SQLite does not support table comments
            $this->loadColumns($table),
            $this->loadIndexes($table)
        );
    }

    private function loadColumns(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info('$table')");
        $cols = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $cols[] = new ColumnSchema(
                name: $col['name'],
                type: $col['type'],
                nullable: !$col['notnull'],
                default: $col['dflt_value'],
                primary: $col['pk'] == 1,
                comment: null // SQLite does not support column comments
            );
        }

        return $cols;
    }

    private function loadIndexes(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA index_list('$table')");
        $indexes = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $idx) {
            $name = $idx['name'];
            $unique = $idx['unique'] == 1;

            $stmt2 = $this->pdo->query("PRAGMA index_info('$name')");
            $columns = array_column($stmt2->fetchAll(PDO::FETCH_ASSOC), 'name');

            $indexes[] = new IndexSchema($name, $unique, $columns);
        }

        return $indexes;
    }
}
