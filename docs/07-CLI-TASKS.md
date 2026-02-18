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

- `Merlin\Cli\TaskNotFoundException`
- `Merlin\Cli\ActionNotFoundException`

## See Also

- [src/Cli/Console.php](../src/Cli/Console.php)
- [src/Cli/Task.php](../src/Cli/Task.php)
