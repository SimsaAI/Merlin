# 🧩 Class: PostgresSchemaProvider

**Full name:** [Merlin\Sync\Schema\PostgresSchemaProvider](../../src/Sync/Schema/PostgresSchemaProvider.php)

## 🚀 Public methods

### __construct() · [source](../../src/Sync/Schema/PostgresSchemaProvider.php#L8)

`public function __construct(PDO $pdo): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$pdo` | PDO | - |  |

**➡️ Return value**

- Type: mixed


---

### listTables() · [source](../../src/Sync/Schema/PostgresSchemaProvider.php#L12)

`public function listTables(): array`

**➡️ Return value**

- Type: array


---

### getTableSchema() · [source](../../src/Sync/Schema/PostgresSchemaProvider.php#L23)

`public function getTableSchema(string $table): Merlin\Sync\Schema\TableSchema`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$table` | string | - |  |

**➡️ Return value**

- Type: [TableSchema](Sync_Schema_TableSchema.md)



---

[Back to the Index ⤴](index.md)
