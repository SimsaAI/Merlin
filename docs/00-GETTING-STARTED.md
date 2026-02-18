# Getting Started

**First steps** - This guide sets up a minimal Merlin project with MVC routing, models, and CLI tasks.

## Requirements

- PHP >= 8.0
- Composer
- `ext-pdo`
- `ext-mbstring`

## Installation

```bash
composer require simsaai/merlin
```

## Recommended Structure

```text
your-project/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Tasks/
│   └── views/
├── public/
│   └── index.php
├── console.php
└── composer.json
```

## Minimal Web Bootstrap

This is the core entry point for web requests. It sets up the application context, configures routing, matches the incoming request, and dispatches it to the appropriate controller.

Create [public/index.php](../public/index.php):

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Merlin\AppContext;
use Merlin\Db\Database;
use Merlin\Http\Response;
use Merlin\Mvc\Dispatcher;
use Merlin\Mvc\Router;

// Initialize application context, database, and view path
$ctx = AppContext::instance();
$ctx->db = new Database('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$ctx->getView()->setPath(__DIR__ . '/../app/views');

// Set up routing
$router = new Router();
$router->add('GET', '/', 'IndexController::indexAction');
$router->add('GET', '/users/{id:int}', 'UserController::viewAction')->setName('user.view');

// Configure dispatcher with namespace and defaults
$dispatcher = new Dispatcher($ctx);
$dispatcher->setBaseNamespace('App\\Controllers');
$dispatcher->setDefaultController('IndexController');
$dispatcher->setDefaultAction('indexAction');

// Get the current request URI and method
$request = $ctx->getRequest();
$path = $request->getPath();
$method = $request->getMethod();

// Match the route and dispatch
$route = $router->match($path, $method);
if ($route === null) {
    // No route matched, return 404 response
    $response = Response::status(404);
} else {
    // Dispatcher will invoke the controller action and store route info in AppContext
    $response = $dispatcher->dispatch($route);
}
// Send the response to the client
$response->send();
```

## Minimal Controller

Controllers handle the business logic for your routes. They receive parameters from the router and return responses in various formats.

Create [app/Controllers/IndexController.php](../app/Controllers/IndexController.php):

```php
<?php
namespace App\Controllers;

use Merlin\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction(): string
    {
        return 'Merlin is running.';
    }
}
```

## Minimal Model

Models represent your database tables and provide an object-oriented way to interact with data. Define public properties that match your table columns.

Create [app/Models/User.php](../app/Models/User.php):

```php
<?php
namespace App\Models;

use Merlin\Mvc\Model;

class User extends Model
{
    public int $id;
    public string $username;
    public string $email;
}
```

Usage:

```php
$user = User::find(1);
$admins = User::findAll(['role' => 'admin']);

$newUser = User::create([
    'username' => 'alice',
    'email' => 'alice@example.com',
]);

$exists = User::exists(['email' => 'alice@example.com']);
```

## CLI Bootstrap

Create [console.php](../console.php):

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Merlin\Cli\Console;

$task = $argv[1] ?? null;
$action = $argv[2] ?? null;
$params = array_slice($argv, 3);

$console = new Console();
// $console->setNamespace('App\\Tasks'); (default is already set to this)
$console->process($task, $action, $params);
```

Run:

```bash
php console.php hello world Merlin
```

## About composer.json

The simplest way to handle dependencies is through a `composer.json` file. This file manages dependencies, autoloading, and project metadata. Composer will automatically generate this file when you run `composer require simsaai/merlin`, but you can customize it as needed.

A minimal `composer.json` for your app might look like:

```json
{
  "require": {
    "simsaai/merlin": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  }
}
```

- The `require` section lists your dependencies, in our case we use Merlin.
- The `autoload` section tells Composer to autoload your app classes from the `app/` directory using PSR-4.
- You can add scripts, dev dependencies, and other metadata as your project grows.

After editing `composer.json`, run:

```bash
composer dump-autoload
```

to update the autoloader.

## Next Steps

- [Architecture](01-ARCHITECTURE.md)
- [MVC Routing](02-MVC-ROUTING.md)
- [Models & ORM](04-MODELS-ORM.md)
- [Database Queries](05-DATABASE-QUERIES.md)
- [CLI Tasks](07-CLI-TASKS.md)
