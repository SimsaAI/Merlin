<?php
namespace Merlin\Sync\Schema;

use PDO;

class PostgresSchemaProvider implements SchemaProvider
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listTables(): array
    {
        $stmt = $this->pdo->query("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTableSchema(string $table): TableSchema
    {
        $columns = $this->loadColumns($table);
        $indexes = $this->loadIndexes($table);
        $comment = $this->loadTableComment($table);

        return new TableSchema(
            $table,
            $comment,
            $columns,
            $indexes
        );
    }

    private function loadTableComment(string $table): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT obj_description(pg_class.oid) AS comment
            FROM pg_class
            WHERE relname = ?
            AND relkind = 'r'
        ");
        $stmt->execute([$table]);

        $comment = $stmt->fetchColumn();
        return $comment !== '' ? $comment : null;
    }

    private function loadColumns(string $table): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                column_name,
                data_type,
                is_nullable,
                column_default,
                pgd.description AS comment,
                (SELECT EXISTS (
                    SELECT 1 FROM information_schema.table_constraints tc
                    JOIN information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
                    WHERE tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY'
                    AND kcu.column_name = c.column_name
                )) AS primary_key
            FROM information_schema.columns c
            LEFT JOIN pg_catalog.pg_statio_all_tables AS st
                ON st.relname = c.table_name
            LEFT JOIN pg_catalog.pg_description pgd
                ON pgd.objoid = st.relid AND pgd.objsubid = c.ordinal_position
            WHERE table_schema = 'public' AND table_name = ?
            ORDER BY ordinal_position
        ");
        $stmt->execute([$table, $table]);

        $cols = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $cols[] = new ColumnSchema(
                name: $col['column_name'],
                type: $col['data_type'],
                nullable: $col['is_nullable'] === 'YES',
                default: $col['column_default'],
                primary: $col['primary_key'] === 't',
                comment: $col['comment'] ?: null
            );
        }

        return $cols;
    }

    private function loadIndexes(string $table): array
    {
        $stmt = $this->pdo->prepare("
            SELECT indexname, indexdef
            FROM pg_indexes
            WHERE schemaname = 'public' AND tablename = ?
        ");
        $stmt->execute([$table]);

        $indexes = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            preg_match('/\((.+)\)/', $row['indexdef'], $m);
            $columns = array_map('trim', explode(',', $m[1]));

            $indexes[] = new IndexSchema(
                name: $row['indexname'],
                unique: str_contains($row['indexdef'], 'UNIQUE'),
                columns: $columns
            );
        }

        return $indexes;
    }
}
