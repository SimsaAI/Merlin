# 🧩 Class: SyncRunner

**Full name:** [Merlin\Sync\SyncRunner](../../src/Sync/SyncRunner.php)

## 🚀 Public methods

### __construct() · [source](../../src/Sync/SyncRunner.php#L17)

`public function __construct(Merlin\Db\DatabaseManager $dbManager): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dbManager` | [DatabaseManager](Db_DatabaseManager.md) | - |  |

**➡️ Return value**

- Type: mixed


---

### syncModel() · [source](../../src/Sync/SyncRunner.php#L35)

`public function syncModel(string $filePath, bool $dryRun = false, string $dbRole = 'read', Merlin\Sync\SyncOptions|null $options = null): Merlin\Sync\SyncResult`

Synchronise a single model file against the database schema.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$filePath` | string | - | Absolute path to the model PHP file |
| `$dryRun` | bool | `false` | When true the file is NOT written; changes are only calculated |
| `$dbRole` | string | `'read'` | Database role to introspect (falls back to default if not registered) |
| `$options` | [SyncOptions](Sync_SyncOptions.md)\|null | `null` |  |

**➡️ Return value**

- Type: [SyncResult](Sync_SyncResult.md)


---

### syncAll() · [source](../../src/Sync/SyncRunner.php#L101)

`public function syncAll(array $modelFiles, bool $dryRun = false, string $dbRole = 'read', Merlin\Sync\SyncOptions|null $options = null): array`

Synchronise multiple model files.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$modelFiles` | array | - | Absolute paths to model PHP files |
| `$dryRun` | bool | `false` |  |
| `$dbRole` | string | `'read'` |  |
| `$options` | [SyncOptions](Sync_SyncOptions.md)\|null | `null` |  |

**➡️ Return value**

- Type: array


---

### listDatabaseTables() · [source](../../src/Sync/SyncRunner.php#L120)

`public function listDatabaseTables(string $dbRole = 'read', string|null $schema = null): array`

Return all table names in the database for the given role and optional schema.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dbRole` | string | `'read'` |  |
| `$schema` | string\|null | `null` | DB schema to scan (PostgreSQL only; pass null to use server default). |

**➡️ Return value**

- Type: array


---

### createModelFile() · [source](../../src/Sync/SyncRunner.php#L133)

`public function createModelFile(string $filePath, string $namespace, string $className, string $tableName, string|null $schema = null): void`

Scaffold a new model file. Throws if the file already exists.

The generated class includes an explicit modelSource() override so the
table name is always unambiguous to subsequent sync operations.
If $schema is given, a modelSchema() override is also generated.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$filePath` | string | - |  |
| `$namespace` | string | - |  |
| `$className` | string | - |  |
| `$tableName` | string | - |  |
| `$schema` | string\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### getModelTableName() · [source](../../src/Sync/SyncRunner.php#L174)

`public function getModelTableName(string $filePath): string|null`

Resolve the table name for a model file without calculating a full diff.

Returns null if the file cannot be parsed or the class is not a valid Model.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$filePath` | string | - |  |

**➡️ Return value**

- Type: string|null



---

[Back to the Index ⤴](index.md)
