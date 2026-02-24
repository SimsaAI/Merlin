# CLI Tasks

**Build command-line tools** â€” Create powerful CLI applications for cron jobs, database migrations, data imports, and maintenance scripts. Tasks are auto-discovered from PSR-4 namespaces, receive parsed options, and can produce color-highlighted output via a simple output API.

Merlin provides `Merlin\Cli\Console` (dispatcher + help system) and `Merlin\Cli\Task` (base class for every task).

---

## Entry Point

Create `console.php` once at the project root:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\Cli\Console;

$console = new Console();
$console->addNamespace('App\\Tasks');   // discover App\Tasks\*Task.php
$console->process($argv[1] ?? null, $argv[2] ?? null, array_slice($argv, 3));
```

Call signature on the command line:

```
php console.php <task> [<action>] [<arg1> <arg2> â€¦] [--option] [--key=value]
```

`Console` separates positional arguments from options automatically before calling the action method. Options are available inside the task via `$this->options` or `$this->opt()`.

---

## Task Discovery

`Console` scans every registered namespace for files matching `*Task.php`, loads them, and registers any class that extends `Merlin\Cli\Task` under a lowercase task name derived from the class name (`DatabaseTask` â†’ `database`).

```php
// Register one or more PSR-4 namespaces (any *Task.php inside will be found)
$console->addNamespace('App\\Tasks');
$console->addNamespace('App\\Admin\\Tasks');

// Register a raw filesystem directory (optionally set up a simple autoloader)
$console->addTaskPath('/path/to/extra/tasks', registerAutoload: true);
```

The built-in `Merlin\Cli\Tasks` namespace (containing `SyncTask`) is **always** included. Discovery resolves paths from `composer.json` PSR-4 entries â€” no path guessing.

### Naming Rules

| Input                         | Resolved to              |
| ----------------------------- | ------------------------ |
| Task argument `database`      | class `DatabaseTask`     |
| Action argument `migrate`     | method `migrateAction()` |
| Action `run-all` or `run_all` | method `runAllAction()`  |

---

## Writing a Task

Extend `Task` and add public `*Action` methods. Positional arguments map to method parameters by position; options are available via `$this->options` / `$this->opt()`.

```php
<?php
namespace App\Tasks;

use Merlin\Cli\Task;

/**
 * Database maintenance utilities.
 *
 * Usage:
 *   php console.php database migrate [<target>] [--direction=<up|down>]
 *   php console.php database seed    [--truncate]
 *
 * Examples:
 *   php console.php database migrate              # migrate up to latest
 *   php console.php database migrate v3 --direction=down
 *   php console.php database seed --truncate
 */
class DatabaseTask extends Task
{
    /**
     * Run database migrations.
     */
    public function migrateAction(string $target = 'latest'): void
    {
        $direction = $this->opt('direction', 'up');
        $this->info("Migrating {$direction} to {$target}â€¦");
        // â€¦ migration logic â€¦
        $this->success("Migration complete.");
    }

    /**
     * Seed the database with initial data.
     */
    public function seedAction(): void
    {
        if ($this->opt('truncate')) {
            $this->warn("Truncating tables before seedingâ€¦");
        }
        // â€¦ seed logic â€¦
        $this->success("Seeding complete.");
    }
}
```

---

## Reading Options

Options (`--flag`, `--key=value`, `--no-flag`) are parsed from the command line **before** calling the action method and are available as an associative array.

```php
// Option --apply        â†’ $this->options['apply']  = true
// Option --no-apply     â†’ $this->options['apply']  = false
// Option --database=read â†’ $this->options['database'] = 'read'
// Option --count 5      â†’ $this->options['count']   = 5   (with coercion on)

// Direct access
$dryRun = !isset($this->options['apply']);
$role   = $this->options['database'] ?? 'read';

// Helper with default
$role = $this->opt('database', 'read');
$dryRun = !$this->opt('apply', false);
```

---

## Output Helpers

All output methods are available inside a task via `$this->â€¦`. They delegate to the `Console` instance, which handles ANSI color automatically (enabled when the terminal supports it, disabled when piped).

| Method                                | Output style                |
| ------------------------------------- | --------------------------- |
| `$this->writeln($text)`               | bare line (no color)        |
| `$this->line($text)`                  | plain white                 |
| `$this->info($text)`                  | cyan                        |
| `$this->success($text)`               | bright green                |
| `$this->warn($text)`                  | bright yellow               |
| `$this->error($text)`                 | bright red `[ERROR]` prefix |
| `$this->muted($text)`                 | gray / dim                  |
| `$this->style($text, 'bold', 'cyan')` | arbitrary named styles      |

### Available Style Names

`bold`, `dim`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`, `gray`,
`bred`, `bgreen`, `byellow`, `bcyan`

```php
public function importAction(string $file = ''): void
{
    $this->info("Importing {$file}â€¦");

    foreach ($rows as $i => $row) {
        if ($row['error']) {
            $this->error("Row {$i}: " . $row['error']);
        } else {
            $this->muted("Row {$i}: ok");
        }
    }

    $this->success("Imported " . count($rows) . " rows.");
}
```

---

## Help System

The built-in help system is powered automatically from **PHPDoc comments** on the task class and its action methods. No extra configuration required.

```
php console.php help                 # color overview: all tasks + actions + descriptions
php console.php help <task>          # detail page: description, actions, usage, examples
```

The detail page parses these doc-comment sections:

```
Usage:    â€” syntax-highlighted with interpreter/task/action/placeholders/options colored
Options:  â€” shown in gray
Examples: â€” syntax-highlighted like Usage
```

Placeholders `<like-this>` are colored bright yellow; `[--options]` are green; `# comments` in examples are gray.

### Example doc-comment

```php
/**
 * Import CSV data into the database.
 *
 * Usage:
 *   php console.php import run <file> [--truncate] [--batch=<size>]
 *
 * Options:
 *   --truncate      Empty the target table before importing
 *   --batch=<size>  Number of rows per insert batch (default: 100)
 *
 * Examples:
 *   php console.php import run data.csv                    # dry-run
 *   php console.php import run data.csv --truncate         # clear first
 *   php console.php import run data.csv --batch=500        # large batches
 */
class ImportTask extends Task { â€¦ }
```

---

## Console Configuration

```php
$console = new Console('myscript.php');  // custom script name shown in help

// Namespace / path registration
$console->addNamespace('App\\Tasks');
$console->addTaskPath('/extra/tasks', registerAutoload: true);

// Colors
$console->enableColors(true);   // force on
$console->enableColors(false);  // force off (e.g. for CI)
$console->hasColors();          // bool

// Color a string directly (useful for custom Console subclasses)
echo $console->style('Hello!', 'bold', 'bgreen');

// Default action (called when no action arg is provided; default: "indexAction")
$console->setDefaultAction('indexAction');
$console->getDefaultAction();

// Parameter type coercion ("5" â†’ 5, "true" â†’ true, "null" â†’ null)
$console->setCoerceParams(true);
$console->shouldCoerceParams();
```

---

## Example with Models

Tasks have full access to your application context:

```php
<?php
namespace App\Tasks;

use App\Models\User;
use Merlin\Cli\Task;

/**
 * User management utilities.
 *
 * Usage:
 *   php console.php user cleanup [--days=<n>]
 *   php console.php user seed
 *
 * Examples:
 *   php console.php user cleanup --days=90
 *   php console.php user seed
 */
class UserTask extends Task
{
    /**
     * Remove users who haven't logged in recently.
     */
    public function cleanupAction(): void
    {
        $days   = $this->opt('days', 30);
        $cutoff = date('Y-m-d', strtotime("-{$days} days"));

        $deleted = User::query()
            ->where('last_login < :cutoff', ['cutoff' => $cutoff])
            ->delete();

        $this->success("Deleted {$deleted} inactive users (cutoff: {$cutoff}).");
    }

    /**
     * Seed the users table with initial data.
     */
    public function seedAction(): void
    {
        User::create(['username' => 'admin', 'email' => 'admin@example.com']);
        $this->success("Seeded users.");
    }
}
```

---

## Error Types

- `Merlin\Cli\Exceptions\TaskNotFoundException`
- `Merlin\Cli\Exceptions\ActionNotFoundException`
- `Merlin\Cli\Exceptions\InvalidTaskException`

---

## See Also

- [src/Cli/Console.php](../src/Cli/Console.php)
- [src/Cli/Task.php](../src/Cli/Task.php)

---

## Built-in: Sync Task

Merlin ships with a ready-made `SyncTask` that keeps your PHP model files in sync with the live database schema (DB â†’ PHP). It is registered under the `Merlin\Cli\Tasks` namespace and is auto-discovered â€” **no extra setup required**.

### Commands

```bash
php console.php sync all   <directory> [options]              # scan a directory of model files
php console.php sync model <file>       [options]              # sync a single model file
php console.php sync make  <ClassName>  <directory> [options]  # scaffold a new model file
```

### Options

| Flag                       | Description                                                                   |
| -------------------------- | ----------------------------------------------------------------------------- |
| _(none)_                   | Dry-run: preview changes without writing files                                |
| `--apply`                  | Write the updated model files to disk                                         |
| `--database=<role>`        | Database role to introspect (default: `read`)                                 |
| `--generate-accessors`     | Generate a camelized getter/setter for each new property                      |
| `--field-visibility=<vis>` | Property visibility: `public` (default), `protected`, or `private`            |
| `--no-deprecate`           | Skip `@deprecated` tags on properties whose columns have been removed         |
| `--create-missing`         | (`sync all` only) Scaffold model files for tables that have no matching model |
| `--namespace=<ns>`         | PHP namespace for scaffolded model files (required with `--create-missing`)   |

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
2. **SchemaProvider** (MySQL / PostgreSQL / SQLite) introspects the live database. Supply `--database=<role>` to target a specific connection.
3. **ModelDiff** computes the difference:
   - Missing PHP property → `AddProperty` (visibility from `--field-visibility`)
   - Missing DB column → `RemoveProperty` (marks `@deprecated`, skipped with `--no-deprecate`)
   - Type mismatch → `UpdatePropertyType`
   - With `--generate-accessors`: an `AddAccessor` is paired with each added property
   - Properties and columns starting with `_` are always skipped
4. **CodeGenerator** applies the operations in-place using string manipulation.
5. **SyncRunner** orchestrates the pipeline and writes the result back to disk.
