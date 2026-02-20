# Merlin Framework – Copilot Instructions

Merlin is a lightweight PHP MVC framework (PHP >= 8.0). Use these instructions when generating code or answering questions about this project.

---

## Project Layout

```
src/
  AppContext.php          # Service container + singleton
  Crypt.php
  Exception.php
  Cli/                    # Console tasks
  Db/                     # Database, Query, ResultSet, Sql, DatabaseManager
  Http/                   # Request, Response, Session, Cookies, UploadedFile
  Mvc/                    # Router, Dispatcher, Controller, Model, ViewEngine
```

---

## AppContext

`Merlin\AppContext` is a singleton service container and the central runtime object.

```php
$ctx = AppContext::instance();          // get (or create) the singleton
AppContext::setInstance($customCtx);    // replace singleton (useful in tests)
```

### Registering / Accessing Services

```php
$ctx->set(MyService::class, new MyService());   // register
$ctx->has(MyService::class);                    // check
$ctx->get(MyService::class);                    // resolve (auto-wires if class exists)
$ctx->tryGet(MyService::class);                 // resolve or null
$ctx->getOrNull(MyService::class);              // already registered only, or null
```

Auto-wiring: if `get($id)` receives a class name that is not registered but exists, `AppContext` will instantiate it via reflection, recursively resolving constructor parameters from the container.

### Built-in Service Accessors

| Method              | Returns                      |
| ------------------- | ---------------------------- |
| `$ctx->request()`   | `Merlin\Http\Request`        |
| `$ctx->view()`      | `Merlin\Mvc\ViewEngine`      |
| `$ctx->session()`   | `Merlin\Http\Session\|null`  |
| `$ctx->cookies()`   | `Merlin\Http\Cookies`        |
| `$ctx->dbManager()` | `Merlin\Db\DatabaseManager`  |
| `$ctx->route()`     | `Merlin\ResolvedRoute\|null` |

### Configuring the View Engine

```php
$ctx->view()->setPath(__DIR__ . '/../views');
$ctx->view()->setLayout('layouts/main');
```

---

## Database – DatabaseManager & Roles

`Merlin\Db\DatabaseManager` manages named database connections called **roles**. The first role registered automatically becomes the default.

```php
$mgr = $ctx->dbManager();

// Register connections
$mgr->set('write', new Database('mysql:host=primary;dbname=app', 'rw', 'secret'));
$mgr->set('read',  fn() => new Database('mysql:host=replica;dbname=app', 'ro', 'secret'));

// Override the default role
$mgr->setDefaultRole('write');

// Retrieve
$mgr->get('read');          // specific role (throws if missing)
$mgr->getOrDefault('read'); // specific role or fallback to default
$mgr->default();            // default role
$mgr->has('analytics');     // check existence
```

Factories are lazy: the callable is invoked only on first use; the resulting `Database` instance is cached.

---

## Models & DB Roles

`Merlin\Mvc\Model` reads from the `read` role and writes to the `write` role by default. These names are looked up via `DatabaseManager::getOrDefault()` (falls back to the default if the specific role is not configured).

### Override per Model Class

```php
// Both read and write to the same role
User::setDefaultRole('analytics');

// Fine-grained control
User::setDefaultReadRole('replica');
User::setDefaultWriteRole('primary');

// Reset to base default (affects all models unless overridden)
Model::setDefaultRole('default');
```

### Single-connection Setup

If you only have one database, just register it under any name – all models will fall through to the default:

```php
$ctx->dbManager()->set('default', new Database('mysql:host=localhost;dbname=app', 'user', 'pass'));
```

---

## Dispatcher & DI

`Merlin\Mvc\Dispatcher` resolves and invokes controllers. It is instantiated **without arguments** and obtains `AppContext` internally via `AppContext::instance()`.

```php
$dispatcher = new Dispatcher();
$dispatcher->setBaseNamespace('App\\Controllers');
$dispatcher->setDefaultController('IndexController');
$dispatcher->setDefaultAction('indexAction');

$response = $dispatcher->dispatch($routeInfo);
```

### Controller Instantiation via DI

Controllers are resolved through `AppContext::get($controllerClass)`. This means:

- Controllers with typed constructor parameters receive their dependencies via auto-wiring.
- Any registered service, including the `DatabaseManager` or custom services, can be injected.

### Action Parameter Injection

Action method parameters are resolved in this order:

1. **Route parameters** – matched by parameter name.
2. **DI (type-hint)** – resolved from `AppContext` by type name.
3. **Default value** – from the method signature.
4. **Nullable** – injected as `null`.

```php
class UserController extends Controller
{
    // $id comes from the route; $db is DI-resolved
    public function viewAction(int $id, DatabaseManager $db): array
    {
        $user = User::find($id);
        return $user ? $user->toArray() : [];
    }
}
```

---

## Router

```php
use Merlin\Mvc\Router;

$router = new Router();
$router->add('GET', '/', 'IndexController::indexAction');
$router->add('GET', '/users/{id:int}', 'UserController::viewAction');

$routeInfo = $router->match($path, $method); // null if no match
```

---

## Minimal Web Bootstrap

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Merlin\AppContext;
use Merlin\Db\Database;
use Merlin\Http\Response;
use Merlin\Mvc\Dispatcher;
use Merlin\Mvc\Router;

$ctx = AppContext::instance();

// Register database connection(s)
$ctx->dbManager()->set('default', new Database('mysql:host=localhost;dbname=myapp', 'user', 'pass'));

// Configure view engine
$ctx->view()->setPath(__DIR__ . '/../views');

// Routing
$router = new Router();
$router->add('GET', '/', 'IndexController::indexAction');

// Dispatcher (no constructor args)
$dispatcher = new Dispatcher();
$dispatcher->setBaseNamespace('App\\Controllers');

$path   = $ctx->request()->getPath();
$method = $ctx->request()->getMethod();

$route = $router->match($path, $method);
if ($route === null) {
    Response::status(404)->send();
} else {
    $dispatcher->dispatch($route)->send();
}
```

---

## CLI Bootstrap

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\Cli\Console;

$console = new Console();
// $console->setNamespace('App\\Tasks');
$console->process($argv[1] ?? null, $argv[2] ?? null, array_slice($argv, 3));
```

---

## Key Conventions

- `AppContext` is always obtained via `AppContext::instance()` – never constructed manually inside framework code.
- Database connections are **always** registered through `DatabaseManager` using named roles; direct `Database` injection into `AppContext` properties is not supported.
- `Dispatcher` takes **no constructor arguments**.
- Models resolve connections through `DatabaseManager::getOrDefault($role)`, so a single registered connection is sufficient for single-DB apps.
- Route parameters are matched **by name**; type-hinted parameters are resolved via DI.
