# 🧩 Class: SyncTask

**Full name:** [Merlin\Cli\Tasks\SyncTask](../../src/Cli/Tasks/SyncTask.php)

CLI task for synchronising PHP model properties from the database schema (DB→PHP).

Usage:
  php console.php sync all  <models-dir> [--apply] [--database=<role>]
  php console.php sync model <file-or-glob> [--apply] [--database=<role>]

By default the task runs in **dry-run** mode and only reports changes.
Pass --apply to write the updated model files to disk.

Examples:
  php console.php sync all  src/Models          # dry-run all models
  php console.php sync all  src/Models --apply  # apply to all models
  php console.php sync model src/Models/User.php --apply

## 🚀 Public methods

### allAction() · [source](../../src/Cli/Tasks/SyncTask.php#L38)

`public function allAction(string $dir = '', string ...$args): void`

Scan a directory recursively, find all PHP files that extend Model,
and sync each one against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dir` | string | `''` | Directory to scan (required) |
| `$args` | string | - | Optional flags: --apply, --database=<role> |

**➡️ Return value**

- Type: void


---

### modelAction() · [source](../../src/Cli/Tasks/SyncTask.php#L78)

`public function modelAction(string $file = '', string ...$args): void`

Sync a single model file against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$file` | string | `''` | Path to the PHP model file (required) |
| `$args` | string | - | Optional flags: --apply, --database=<role> |

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
