# 🧩 Class: SyncTask

**Full name:** [Merlin\Cli\Tasks\SyncTask](../../src/Cli/Tasks/SyncTask.php)

CLI task for synchronising PHP model properties from the database schema (DB→PHP)
and for scaffolding new model files from database tables.

Usage:
  php console.php sync all   <models-dir> [--apply] [--database=<role>]
                             [--generate-accessors] [--field-visibility=<public|protected|private>]
                             [--no-deprecate] [--create-missing] [--namespace=<ns>]
  php console.php sync model <file>        [--apply] [--database=<role>]
                             [--generate-accessors] [--field-visibility=<public|protected|private>]
                             [--no-deprecate]
  php console.php sync make  <ClassName>   <directory> [--apply] [--database=<role>]
                             [--namespace=<ns>] [--generate-accessors]
                             [--field-visibility=<public|protected|private>] [--no-deprecate]

By default the task runs in **dry-run** mode and only reports changes.
Pass --apply to write the updated model files to disk.

Examples:
  php console.php sync all  src/Models                                  # dry-run
  php console.php sync all  src/Models --apply                          # apply
  php console.php sync all  src/Models --apply --generate-accessors     # with accessors
  php console.php sync all  src/Models --apply --field-visibility=protected
  php console.php sync all  src/Models --apply --no-deprecate
  php console.php sync all  src/Models --apply --create-missing --namespace=App\\Models
  php console.php sync model src/Models/User.php --apply
  php console.php sync make  User src/Models --namespace=App\\Models --apply

## 🚀 Public methods

### allAction() · [source](../../src/Cli/Tasks/SyncTask.php#L53)

`public function allAction(string $dir = '', string ...$args): void`

Scan a directory recursively, find all PHP files that extend Model,
and sync each one against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dir` | string | `''` | Directory to scan (required) |
| `$args` | string | - | Optional flags: --apply, --database=<role>, --generate-accessors,<br>--field-visibility=<vis>, --no-deprecate, --create-missing, --namespace=<ns> |

**➡️ Return value**

- Type: void


---

### modelAction() · [source](../../src/Cli/Tasks/SyncTask.php#L139)

`public function modelAction(string $file = '', string ...$args): void`

Sync a single model file against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$file` | string | `''` | Path to the PHP model file (required) |
| `$args` | string | - | Optional flags: --apply, --database=<role>, --generate-accessors,<br>--field-visibility=<vis>, --no-deprecate |

**➡️ Return value**

- Type: void


---

### makeAction() · [source](../../src/Cli/Tasks/SyncTask.php#L177)

`public function makeAction(string $className = '', string $dir = '', string ...$args): void`

Scaffold a new model class from a database table and immediately sync its properties.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$className` | string | `''` | Short class name without namespace (e.g. User) |
| `$dir` | string | `''` | Target directory for the new file |
| `$args` | string | - | Optional flags: --apply, --database=<role>, --namespace=<ns>,<br>--generate-accessors, --field-visibility=<vis>, --no-deprecate |

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
