# Merlin MVC Framework

![Merlin Logo](docs/images/merlin-logo-text-opt.svg)

A lightweight, fast PHP framework for building modern MVC web applications and CLI tools. Merlin combines the best ideas from frameworks like Phalcon, CodeIgniter, and Laravel into a minimal yet powerful toolkit.

## Why Merlin?

**Lightweight & Fast** - Minimal dependencies and overhead. No bloat, just what you need.

**Modern PHP** - Built for PHP 8.0+, embracing type hints, named arguments, and modern patterns.

**Unified Query Builder** - One consistent, fluent API for all database operations, whether you're using models or raw queries.

**Flexible Architecture** - Use as much or as little as you need. Mix and match components freely.

**Secure by Default** - Prepared statements everywhere, CSRF protection, encryption helpers, and security best practices built in.

**Developer Friendly** - Intuitive APIs, clear error messages, and comprehensive documentation.

## Features

### MVC Stack

- **Router** - Fast pattern matching with named routes, parameter validation, and middleware support
- **Controllers** - Clean action-based controllers with dependency injection
- **Dispatcher** - Flexible request dispatching with middleware pipeline
- **ViewEngine** - Fast PHP-based templating with layout support

### Database & ORM

- **Query Builder** - Unified fluent interface for SELECT, INSERT, UPDATE, DELETE
- **Active Record Models** - Expressive model API with relationships and validation
- **Prepared Statements** - SQL injection protection by default
- **Read/Write Splitting** - Built-in support for master/replica database setups
- **Connection Pooling** - Automatic reconnection and connection management
- **Schema Introspection** - Introspect database schema for dynamic models and migrations

### HTTP Utilities

- **Request** - Normalized access to GET, POST, headers, and file uploads
- **Response** - Fluent response building with JSON, redirects, and status codes
- **Session** - Simple session management with pluggable storage handlers
- **Cookies** - Easy cookie handling with encryption support
- **Middleware** - Composable request/response filters

### CLI Tools

- **Console** - Powerful CLI dispatcher with:
  - Auto-discovery of tasks from namespaces
  - Flexible task grouping and custom namespaces
  - Rich color output and styled help pages
  - Option parsing and argument separation
  - Built-in help and task listing
- **ModelSync Task (`model-sync`)** - Built-in CLI task for synchronizing PHP models with the database schema, generating migration/migration-like changes and optionally applying them.

### Additional Features

- **Security** - CSRF tokens, password hashing, encryption (Sodium/OpenSSL)
- **Logging** - Event-based logging hooks for database and application events
- **Pagination** - Built-in query pagination support
- **Exception Handling** - Structured exception hierarchy
- **AppContext** - Centralized service container for shared resources

## Requirements

- PHP >= 8.0
- PDO extension (`ext-pdo`)
- Multibyte String extension (`ext-mbstring`)
- Optional: Sodium or OpenSSL extension for advanced encryption features

(PDO driver support is implemented for MySQL, PostgreSQL and SQLite. Other drivers may work but are not officially tested.)

## Installation

Install via Composer:

```bash
composer require simsaai/merlin
```

## Quick Start

### Web Application (MVC)

Create a simple web application with routing and controllers:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\AppContext;
use Merlin\Db\Database;
use Merlin\Http\Response;
use Merlin\Mvc\Dispatcher;
use Merlin\Mvc\Router;

// Application context holds shared services
// Dispatcher, Controllers, Query Builders and Models access the AppContext
// singleton for database connections, request data, etc. like in this example.
// This allows flexible configuration and easy access to services throughout
// the application without tight coupling.
$ctx = AppContext::instance();

// Register database connection as a lazy service
// The label 'default' is used to identify this connection. The first
// registered connection becomes the default connection. You can register
// multiple connections with different names and roles (e.g. 'read', 'write')
// for read/write splitting. The closure allows for lazy initialization,
// so the connection is only created when first accessed.
$ctx->dbManager()->set('default',
    fn() => new Database('mysql:host=localhost;dbname=myapp', 'user', 'pass')
);

// Configure routing
// Define routes with HTTP method, path pattern, and controller action.
// The pattern can include named parameters (e.g. {name}) which will be
// passed to the controller action as arguments. The controller action is
// specified as 'ControllerClass::methodName' or as array syntax
// ['controller' => 'ControllerClass', 'action' => 'methodName'] for more
// complex cases.
$router = $ctx->router();
$router->add('GET', '/hello/{name}', 'IndexController::helloAction');

// Match the incoming request
$path = $ctx->request()->getPath();
$method = $ctx->request()->getMethod();
$route = $router->match($path, $method);

if ($route === null) {
    // No route matched - return 404
    Response::status(404)->send();
    exit;
}

// Dispatcher handles controller resolution and middleware
$dispatcher = new Dispatcher();
// Dispatch the request to the appropriate controller action
$response = $dispatcher->dispatch($route);
// Send the response to the client
$response->send();
```

Controller example:

```php
<?php
namespace App\Controllers;

use Merlin\Mvc\Controller;

class IndexController extends Controller
{
    public function helloAction(string $name): string
    {
        return "Hello, {$name}!";
    }
}
```

### Working with Models

Define and use Active Record style models:

```php
class User extends \Merlin\Mvc\Model
{
    public int $id;
    public string $username;
    public string $email;
}

// Find by primary key
$user = User::find(1);

// Update and save
$user->email = 'john@example.com';
$user->save();

// Create new record
$newUser = User::create([
    'username' => 'jane',
    'email' => 'jane@example.com',
]);
$newUser->save();

// Delete record
$newUser->delete();

// Count records
$count = User::count(['status' => 'active']);

// Check existence
$exists = User::exists(['email' => 'john@example.com']);

// Query with conditions
$users = User::query()
    // Column/value style
    ->where('status', 'active')
    // Inline parameters
    ->where('status = :status', ['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->select();

// Insert data
User::query()->insert([
    'username' => 'john',
    'email' => 'john@example.com',
]);

// Update records
User::query()
    ->where('id', 42)
    ->update(['status' => 'inactive']);

// Delete records
User::query()->where('status', 'spam')->delete();
```

### Advanced Query Builder

Build complex queries with joins, subqueries, and aggregations.

#### Using Models and Sql Functions

```php
use Merlin\Db\Sql;

// Subquery: select the latest order date for each user
$latestOrder = Sql::subquery(
    Order::query('o2')
        ->where('o2.user_id = u.id')
        ->orderBy('o2.created_at DESC')
        ->limit(1)
        ->select('o2.created_at')
)->as('latest_order');

$results = Order::query('o')
    ->join(User::class, 'u', 'o.user_id = u.id')
    ->where('o.status', 'completed')
    ->where('o.total >', 100)
    ->groupBy('u.id')
    ->having('COUNT(*) >', 5)
    ->select([
        'u.username',
        Sql::raw('COUNT(*)')->as('order_count'),
        Sql::func('SUM', 'o.total')->as('total_spent'),
        $latestOrder,
    ]);
```

#### Subqueries as Sources

A `Query` instance can be passed directly to `->from()` or to any join method. The subquery is wrapped in parentheses automatically and its bind parameters are propagated to the outer query — no manual merging required.

```php
use Merlin\Db\Query;

// Build the subquery independently
$completedOrders = Order::query()
    ->where('status', 'completed')
    ->where('created_at > :since', ['since' => '2025-01-01'])
    ->groupBy('user_id')
    ->columns(['user_id', 'SUM(total) AS total_spent']);

// Use it as a derived table with ->from()
$topBuyers = Query::new()
    ->from($completedOrders, 'co')
    ->where('co.total_spent >', 500)
    ->orderBy('co.total_spent DESC')
    ->select();

// Or join it alongside another table
$report = User::query()
    ->leftJoin($completedOrders, 'co', 'co.user_id = u.id')
    ->columns(['u.username', 'co.total_spent'])
    ->select();
```

#### Using ModelMapping for Dynamic Model References

`ModelMapping` allows you to refer to models in queries without creating actual PHP model classes. This is useful when you want the convenience of model names in your queries but don't need the full Active Record functionality.

Set up a `ModelMapping` to map model names to their source tables and optional schemas:

```php
use Merlin\Db\Query;
use Merlin\Mvc\ModelMapping;

// Create model mappings
$mapping = ModelMapping::fromArray([
    'User' => 'users',                    // simple: "Model" => "table"
    'Order' => ['source' => 'orders', 'schema' => 'public'],  // with schema
    'Product' => ['source' => 'products'],
]);

// Register the mapping globally
Query::setModelMapping($mapping);
Query::useModels(true);

// Now you can reference models by name in queries
$results = Query::new()
    ->from('User', 'u')
    ->join('Order', 'o', 'u.id = o.user_id')
    ->where('o.status', 'completed')
    ->columns(['u.username', 'o.id', 'o.total'])
    ->select();
```

You can also enable automatic table name pluralization for models:

```php
ModelMapping::usePluralTableNames(true);

// Now "User" automatically maps to "users", "Order" to "orders", etc.
// (without needing explicit mappings for every model)
$mapping = ModelMapping::fromArray([
    'User' => true,    // uses auto-pluralized table name
]);

Query::setModelMapping($mapping);
Query::useModels(true);
```

#### Using the Query Builder Directly on Tables

For raw queries without models, you can use the Query builder directly.
This is useful for complex queries that don't fit the Active Record pattern or when you want more control over the SQL being generated. The API is the same as the model query builder, but you start with `Query::new()` and specify the table manually.

```php
use Merlin\Db\Query;


Query::useModels(false);

$results = Query::new()
    ->table('orders o')
    ->join('users u', 'o.user_id = u.id')
    ->where('o.status', 'completed')
    ->where('o.total >', 100)
    ->groupBy('u.id')
    ->having('COUNT(*) >', 5)
    ->select([
        'u.username',
        'COUNT(*) as order_count',
        'SUM(o.total) as total_spent'
    ]);
```

### CLI Tasks

Build command-line tools and scripts. `Console` auto-discovers tasks from PSR-4 namespaces, parses options, and renders color-highlighted help pages.

**console.php** — minimal entry point:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\Cli\Console;

$console = new Console();
// App\Tasks is included automatically; add other namespaces if needed:
// $console->addNamespace('App\\Admin\\Tasks');
$console->process($argv[1] ?? null, $argv[2] ?? null, array_slice($argv, 3));
```

**Create a task** — extend `Task`, add `*Action` methods:

```php
<?php
namespace App\Tasks;

use Merlin\Cli\Task;

/**
 * Database maintenance utilities.
 *
 * Usage:
 *   php console.php database migrate [--direction=<up|down>]
 *
 * Examples:
 *   php console.php database migrate            # migrate up
 *   php console.php database migrate --direction=down
 */
class DatabaseTask extends Task
{
    public function migrateAction(string $target = 'latest'): void
    {
        $direction = $this->option('direction', 'up');
        $this->info("Migrating {$direction} to {$target}…");
        // migration logic here
        $this->success("Done.");
    }
}
```

**Run tasks** from the command line — options are separated from positional arguments automatically:

```bash
php console.php database migrate latest --direction=down
php console.php help               # overview with all tasks and actions
php console.php help database      # detail page for one task
```

**Using Built-in Tasks**

Merlin includes a built-in tasks for common development operations. The `model-sync` task synchronizes your PHP models with the database schema and supports these actions and options:

```bash
# Auto-discover App\Models via PSR-4 and preview differences (no args needed)
php console.php model-sync all

# Or target an explicit directory
php console.php model-sync all src/Models

# Apply detected changes (use --apply to write files)
php console.php model-sync all src/Models --apply [--database=<role>] [--generate-accessors] [--create-missing]

# Run sync for a single model file
php console.php model-sync model src/Models/User.php [--apply] [--database=<role>]

# Scaffold a new model – auto-discovers App\Models directory
php console.php model-sync make Order

# Or scaffold into an explicit directory
php console.php model-sync make Order src/Models [--namespace=App\\Models] [--apply]
```

When no directory is provided, `model-sync all` and `model-sync make` automatically resolve the target by looking for `App\Models` in the PSR-4 entries of `composer.json`, then falling back to common paths (`app/Models`, `src/Models`).

## Project Structure

Recommended directory layout for Merlin applications:

```text
your-project/
├── app/
│   ├── Controllers/     # MVC controllers
│   ├── Models/          # Database models
│   ├── Tasks/           # CLI tasks
│   ├── Middleware/      # Custom middleware
├── config/
│   └── database.php     # Configuration files
├── public/
│   ├── index.php        # Web entry point
│   ├── css/
│   └── js/
├── views/               # View templates
├── console.php          # CLI entry point
├── composer.json
└── .gitignore
```

## Documentation

Comprehensive guides and references:

- **[Getting Started](docs/00-GETTING-STARTED.md)** - Set up your first Merlin project
- **[Architecture](docs/01-ARCHITECTURE.md)** - Understand core components and design principles
- **[MVC Routing](docs/02-MVC-ROUTING.md)** - Define routes, patterns, and middleware
- **[Controllers & Views](docs/03-CONTROLLERS-VIEWS.md)** - Build controllers and render views
- **[Models & ORM](docs/04-MODELS-ORM.md)** - Work with Active Record models
- **[Database Queries](docs/05-DATABASE-QUERIES.md)** - Master the query builder
- **[HTTP Request](docs/06-HTTP-REQUEST.md)** - Handle requests, uploads, and headers
- **[CLI Tasks](docs/07-CLI-TASKS.md)** - Create command-line tools
- **[Security](docs/08-SECURITY.md)** - Best practices and security features
- **[Logging](docs/09-LOGGING.md)** - Application and database logging
- **[Cookbook](docs/10-COOKBOOK.md)** - Practical recipes and examples
- **[API Reference](docs/api/index.md)** - Complete API documentation

## Key Concepts

### AppContext - Service Container

Centralized access to shared services via a singleton service container:

```php
use Merlin\AppContext;
use Merlin\Db\Database;

$ctx = AppContext::instance();

// Register database connection(s)
$ctx->dbManager()->set('default',
    fn() => new Database('mysql:host=localhost;dbname=app', 'user', 'pass')
);

// Configure services
$ctx->view()->setPath(__DIR__ . '/views');

// Access services anywhere
$ctx = AppContext::instance();
$request = $ctx->request();
$cookies = $ctx->cookies();
```

### Middleware Pipeline

Add custom logic to the request/response cycle:

```php
$dispatcher = new Dispatcher();
$dispatcher->addMiddleware(new AuthMiddleware());
$dispatcher->addMiddleware(new SessionMiddleware());
$response = $dispatcher->dispatch($route);
```

### Read/Write Database Splitting

Separate read and write connections for scalability:

```php
$mgr = AppContext::instance()->dbManager();
$mgr->set('write', new Database('mysql:host=master;dbname=app', 'user', 'pass'));
$mgr->set('read', new Database('mysql:host=replica;dbname=app', 'user', 'pass'));

// Models and queries automatically route reads to 'read' role, writes to
// 'write' role. Falls back to default if a specific role is missing
```

## Development

### Running Tests

Merlin uses PHPUnit for testing:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Db/QueryBuilderTest.php

# Run with coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html coverage/
```

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

When contributing:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for new functionality
4. Ensure all tests pass
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Examples

Check out the `examples/` directory for complete working examples:

- **[AdvancedQueryBuilderExample.php](examples/AdvancedQueryBuilderExample.php)** - Complex queries with joins, subqueries, window functions, and aggregations. Perfect for learning sophisticated query patterns.
- **[CompositeKeyExamples.php](examples/CompositeKeyExamples.php)** - Working with models that have composite primary keys, such as many-to-many junction tables and multi-tenant databases.
- **[ModelLoadMethodsExample.php](examples/ModelLoadMethodsExample.php)** - Using convenience methods like `find()`, `findOne()`, `findAll()`, `exists()`, and `count()` for retrieving model data.
- **[ReadWriteConnectionExample.php](examples/ReadWriteConnectionExample.php)** - Setting up separate read and write database connections for master/replica configurations and improved scalability.
- **[SaveCreateUpdateExample.php](examples/SaveCreateUpdateExample.php)** - Complete CRUD operations including `create()`, `update()`, `save()`, `delete()`, and tracking changes with `hasChanged()`.
- **[SqlNodeExample.php](examples/SqlNodeExample.php)** - Advanced SQL expressions using the `Sql` class for raw SQL, functions, subqueries, and complex conditions within the query builder.
- **[SyncExample/](examples/SyncExample/)** - A CLI application example demonstrating task auto-discovery, custom namespaces, and the built-in `model-sync` task features.

## Philosophy

Merlin is designed with these principles:

- **Simplicity over magic** - Explicit is better than implicit
- **Performance** - Minimal overhead and memory footprint
- **Standards** - PSR-compliant where applicable
- **Flexibility** - Use what you need, ignore the rest
- **Security** - Secure by default, not as an afterthought

## Acknowledgments

Merlin draws inspiration from:

- **Phalcon** - Speed and C-based architecture concepts
- **CodeIgniter** - Simplicity and developer-friendly APIs
- **Laravel** - Elegant syntax and query builder design

## About the Name

Merlins are small falcons known for their speed, agility, and hunting precision. These characteristics reflect the goals of the Merlin framework: a lightweight, fast, and focused MVC system. The name is also a deliberate reference to Phalcon, which strongly influenced Merlin’s design.

## License

MIT License - see [LICENSE](LICENSE) file for details.

---

**Made with 💖 and ⚡ by developers, for developers.**
