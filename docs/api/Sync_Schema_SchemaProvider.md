# 🔌 Interface: SchemaProvider

**Full name:** [Merlin\Sync\Schema\SchemaProvider](../../src/Sync/Schema/SchemaProvider.php)

## 🚀 Public methods

### listTables() · [source](../../src/Sync/Schema/SchemaProvider.php#L9)

`public function listTables(): array`

**➡️ Return value**

- Type: array
- Description: Liste aller Tabellen im aktuellen Schema


---

### getTableSchema() · [source](../../src/Sync/Schema/SchemaProvider.php#L14)

`public function getTableSchema(string $table): Merlin\Sync\Schema\TableSchema`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$table` | string | - |  |

**➡️ Return value**

- Type: [TableSchema](Sync_Schema_TableSchema.md)
- Description: Struktur einer Tabelle



---

[Back to the Index ⤴](index.md)
