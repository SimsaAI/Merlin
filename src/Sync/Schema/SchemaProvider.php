<?php
namespace Merlin\Sync\Schema;

interface SchemaProvider
{
    /**
     * @return string[]  Liste aller Tabellen im aktuellen Schema
     */
    public function listTables(): array;

    /**
     * @return TableSchema  Struktur einer Tabelle
     */
    public function getTableSchema(string $table): TableSchema;
}

class TableSchema
{
    public function __construct(
        public string $name,
        public ?string $comment,
        /** @var ColumnSchema[] */
        public array $columns,
        /** @var IndexSchema[] */
        public array $indexes
    ) {
    }
}

class ColumnSchema
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public mixed $default,
        public bool $primary,
        public ?string $comment
    ) {
    }
}

class IndexSchema
{
    public function __construct(
        public string $name,
        public bool $unique,
        /** @var string[] */
        public array $columns
    ) {
    }
}
