# CLI Tasks

**Build command-line tools** - Create powerful CLI applications for cron jobs, database migrations, data imports, and maintenance scripts. Learn how to structure tasks, parse parameters, and integrate with your application context.

Merlin provides a lightweight CLI dispatcher with `Merlin\Cli\Console` and task base class `Merlin\Cli\Task`.

## Task Class

Tasks are PHP classes with action methods. Each public method ending in 'Action' becomes a runnable command with automatic parameter mapping.

```php
<?php
namespace App\Tasks;

use Merlin\Cli\Task;

class HelloTask extends Task
{
    public function worldAction(string $name = 'World'): void
    {
        echo "Hello, {$name}!\n";
    }
}
```

## Console Entry Script

The console entry point dispatches command-line arguments to task classes. Set it up once and use it for all your CLI needs.

Create `console.php` as your CLI entrypoint:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\Cli\Console;

$task = $argv[1] ?? null;
$action = $argv[2] ?? null;
$params = array_slice($argv, 3);

$console = new Console();
$console->setNamespace('App\\Tasks');
$console->process($task, $action, $params);
```

## Running a Task

Tasks are invoked from the command line with a simple syntax: task name, action name, and any parameters.

```bash
php console.php hello world Merlin
```

## Naming Rules

The Console automatically converts kebab-case and snake_case arguments to PSR-4 class names and camelCase methods.

- Task argument `database` resolves to class `DatabaseTask`
- Action argument `migrate` resolves to method `migrateAction()`
- Separators (`-`, `_`, `.`) are camelized automatically

## Example with Models

Tasks have full access to your application context, making them perfect for database operations, data imports, and maintenance scripts.

```php
<?php
namespace App\Tasks;

use App\Models\User;
use Merlin\Cli\Task;

class UserTask extends Task
{
    public function seedAction(): void
    {
        User::query()->insert([
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);

        echo "Seeded users\n";
    }

    public function cleanupAction(int $days = 30): void
    {
        $cutoff = date('Y-m-d', strtotime("-{$days} days"));

        $deleted = User::query()
            ->where('last_login < :cutoff', ['cutoff' => $cutoff])
            ->delete();

        echo "Deleted {$deleted} users\n";
    }
}
```

## Error Types

- `Merlin\Cli\Exceptions\TaskNotFoundException`
- `Merlin\Cli\Exceptions\ActionNotFoundException`
- `Merlin\Cli\Exceptions\InvalidTaskException`

## See Also

- [src/Cli/Console.php](../src/Cli/Console.php)
- [src/Cli/Task.php](../src/Cli/Task.php)

## Built-in: Sync Task

Merlin ships with a ready-made `SyncTask` that keeps your PHP model files in sync with the live database schema (DB → PHP). It is available in the `Merlin\Cli\Tasks` namespace.

### Activate in your console entry point

```php
$console = new Console();
$console->setNamespace('\\Merlin\\Cli\\Tasks');
$console->process($argv[1] ?? null, $argv[2] ?? null, array_slice($argv, 3));
```

### Commands

```
php console.php sync all   <directory> [options]   # scan a directory of model files
php console.php sync model <file>       [options]   # sync a single model file
php console.php sync make  <ClassName>  <directory> [options]   # scaffold a new model file
```

### Options

| Flag | Description |
|---|---|
| _(none)_ | Dry-run: preview changes without writing files |
| `--apply` | Write the updated model files to disk |
| `--database=<role>` | Database role to introspect (default: `read`) |
| `--generate-accessors` | Generate a camelized getter/setter for each new property |
| `--field-visibility=<vis>` | Property visibility: `public` (default), `protected`, or `private` |
| `--no-deprecate` | Skip `@deprecated` tags on properties whose columns have been removed |
| `--create-missing` | (`sync all` only) Scaffold model files for tables that have no matching model |
| `--namespace=<ns>` | PHP namespace for scaffolded model files (required with `--create-missing`) |

### Examples

```bash
# Preview changes for all models in a directory
php console.php sync all Models

# Apply changes
php console.php sync all Models --apply

# Apply with protected properties and accessor methods
php console.php sync all Models --apply --generate-accessors --field-visibility=protected

# Scaffold models for any DB tables not yet represented, then sync them
php console.php sync all Models --apply --create-missing --namespace=App\\Models

# Sync a single file
php console.php sync model Models/User.php --apply

# Scaffold a brand-new model from a DB table and immediately populate its properties
php console.php sync make Order Models --namespace=App\\Models --apply
```

### How it works

1. **ModelParser** tokenises the PHP source and extracts class properties + metadata.
2. **SchemaProvider** (MySQL / PostgreSQL / SQLite) introspects the live database. Supply `--database=<role>` to target a specific connection; the `$schema` parameter is forwarded automatically for PostgreSQL.
3. **ModelDiff** computes the difference:
   - Missing PHP property → `AddProperty` (visibility controlled by `--field-visibility`)
   - Missing DB column → `RemoveProperty` (marks property `@deprecated`, skipped with `--no-deprecate`)
   - Type mismatch → `UpdatePropertyType`
   - With `--generate-accessors`: an `AddAccessor` is paired with each added property, producing a camelized dual-purpose getter/setter
   - Properties and columns starting with `_` are always skipped
4. **CodeGenerator** applies the operations in-place using string manipulation.
5. **SyncRunner** orchestrates the pipeline and writes the result back to disk.
