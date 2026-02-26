# 🧩 Class: ModelSyncTask

**Full name:** [Merlin\Cli\Tasks\ModelSyncTask](../../src/Cli/Tasks/ModelSyncTask.php)

CLI task for synchronising PHP model properties from the database schema (DB→PHP)
and for scaffolding new model files from database tables.

Usage:
  model-sync all   <models-dir> [--apply] [--database=<role>]
                                [--generate-accessors] [--no-deprecate]
                                [--field-visibility=<public|protected|private>]
                                [--create-missing] [--namespace=<ns>]
  model-sync model <file> [--apply] [--database=<role>]
                                [--generate-accessors]
                                [--field-visibility=<public|protected|private>]
                                [--no-deprecate]
  model-sync make  <ClassName>  <directory> [--apply]
                                [--database=<role>] [--namespace=<ns>]
                                [--generate-accessors] [--no-deprecate]
                                [--field-visibility=<public|protected|private>]

By default the task runs in **dry-run** mode and only reports changes.
Pass --apply to write the updated model files to disk.

Options:
  --database=<role>           Database role to use for schema introspection
                              (default: "read")
  --generate-accessors        Also generate getter/setter methods for each
                              property
  --field-visibility=<vis>    Visibility for generated properties (default:
                              "public")
  --no-deprecate              Don't add @deprecated tags to removed
                              properties
  --create-missing            Create new model files for tables that don't
                              have a corresponding model yet
  --namespace=<ns>            Namespace to use when creating new model files
                              (required if --create-missing is used)

Examples:
  php console.php model-sync all  src/Models                              # dry-run
  php console.php model-sync all  src/Models --apply                      # apply
  php console.php model-sync all  src/Models --apply --generate-accessors # with accessors
  php console.php model-sync all  src/Models --apply --field-visibility=protected
  php console.php model-sync all  src/Models --apply --no-deprecate
  php console.php model-sync all  src/Models --apply --create-missing --namespace=App\\Models
  php console.php model-sync model src/Models/User.php --apply
  php console.php model-sync make  User src/Models --namespace=App\\Models --apply

## 🔐 Public Properties

- `public` [Console](Cli_Console.md) `$console` · [source](../../src/Cli/Tasks/ModelSyncTask.php)
- `public` array `$options` · [source](../../src/Cli/Tasks/ModelSyncTask.php)

## 🚀 Public methods

### allAction() · [source](../../src/Cli/Tasks/ModelSyncTask.php#L68)

`public function allAction(string $dir = ''): void`

Scan a directory recursively, find all PHP files that extend Model,
and sync each one against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dir` | string | `''` | Directory to scan (required) |

**➡️ Return value**

- Type: void


---

### modelAction() · [source](../../src/Cli/Tasks/ModelSyncTask.php#L152)

`public function modelAction(string $file = ''): void`

Sync a single model file against the database.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$file` | string | `''` | Path to the PHP model file (required) |

**➡️ Return value**

- Type: void


---

### makeAction() · [source](../../src/Cli/Tasks/ModelSyncTask.php#L188)

`public function makeAction(string $className = '', string $dir = ''): void`

Scaffold a new model class from a database table and immediately sync its properties.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$className` | string | `''` | Short class name without namespace (e.g. User) |
| `$dir` | string | `''` | Target directory for the new file |

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
