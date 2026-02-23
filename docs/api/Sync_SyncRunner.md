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

`public function syncModel(string $filePath, bool $dryRun = false, string $dbRole = 'read'): Merlin\Sync\SyncResult`

Synchronise a single model file against the database schema.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$filePath` | string | - | Absolute path to the model PHP file |
| `$dryRun` | bool | `false` | When true the file is NOT written; changes are only calculated |
| `$dbRole` | string | `'read'` | Database role to introspect (falls back to default if not registered) |

**➡️ Return value**

- Type: [SyncResult](Sync_SyncResult.md)


---

### syncAll() · [source](../../src/Sync/SyncRunner.php#L101)

`public function syncAll(array $modelFiles, bool $dryRun = false, string $dbRole = 'read'): array`

Synchronise multiple model files.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$modelFiles` | array | - | Absolute paths to model PHP files |
| `$dryRun` | bool | `false` |  |
| `$dbRole` | string | `'read'` |  |

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](index.md)
