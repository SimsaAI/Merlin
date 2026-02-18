# Controllers & Views

**Build your application logic and presentation** - Learn how to create controllers that handle requests, use dependency injection, work with the view engine, and render templates with layouts. Includes lifecycle hooks, helper methods, and best practices.

Controllers coordinate request handling and return values. View rendering is handled by `Merlin\Mvc\ViewEngine`.

## Controller Basics

```php
<?php
namespace App\Controllers;

use Merlin\Mvc\Controller;

class UserController extends Controller
{
    public function indexAction(): string
    {
        return 'User index';
    }
}
```

## Available Controller Helpers

Controllers come with several convenience methods and properties to access common services without manual lookup.

From `Merlin\Mvc\Controller`:

- `$this->getContext()` returns `AppContext`
- `$this->request` is injected (`Merlin\Http\Request`)
- `view()` returns the current `ViewEngine`
- `session()` returns current session (or `null`)
- `beforeAction()` / `afterAction()` hooks
- `$middleware` and `$actionMiddleware` configuration arrays

## Returning Responses

One of Merlin's conveniences is flexible return types. The Dispatcher automatically converts controller return values into proper HTTP responses, so you can return simple strings, arrays, or full Response objects depending on your needs.

Controller actions can return values handled by `Dispatcher`:

- `Merlin\Http\Response`
- `array` / `JsonSerializable` (auto JSON response)
- `string` (text response)
- `int` (status response)
- `null` (`204 No Content`)

```php
use Merlin\Http\Response;

class HealthController extends Controller
{
    public function pingAction(): array
    {
        return ['ok' => true];
    }

    public function movedAction(): Response
    {
        return Response::redirect('/new-location');
    }
}
```

## ViewEngine Basics

The ViewEngine provides simple PHP-based templating with layout support. Views are regular PHP files where you have access to any variables you pass in. This keeps things simple and gives you full PHP power when needed.

Configure view service in bootstrap:

```php
use Merlin\AppContext;
use Merlin\Mvc\ViewEngine;

AppContext::instance()->view = (new ViewEngine())
    ->setPath(__DIR__ . '/../views')
    ->setExtension('php')
    ->setLayout('layouts/main');
```

Render in controller:

```php
class PageController extends Controller
{
    public function homeAction(): string
    {
        return $this->view()->render('home/index', [
            'title' => 'Home',
            'message' => 'Welcome',
        ]);
    }
}
```

## View Name Resolution

The ViewEngine resolves view names to filesystem paths using the following rules:

**Relative view names** use dot-notation that gets converted to directory separators:

- `users.index` becomes `users/index.php`
- `home.index` becomes `home/index.php`
- `admin.users.edit` becomes `admin/users/edit.php`

**Namespaced views** use `namespace::view.name` syntax where dots after `::` are also converted:

- `admin::dashboard.index` becomes `{namespace-path}/dashboard/index.php`
- `partials::user.card` becomes `{namespace-path}/user/card.php`

**Relative paths starting with a dot** are used as literal paths (not converted):

- `./partials/header` stays as `./partials/header.php`
- `../shared/footer` stays as `../shared/footer.php`

**Absolute paths** are used as-is without conversion:

- `/var/www/views/custom.php` (Unix)
- `C:/app/views/custom.php` (Windows)
- `\\server\share\views\custom.php` (UNC)

## View Variables

```php
$view = $this->view();
$view->setVar('title', 'Users');
$view->setVars(['users' => $users, 'count' => count($users)]);

$html = $view->render('user/index');
```

## View Namespaces

```php
$view = $this->view();
$view->addNamespace('admin', __DIR__ . '/../app/views/admin');

echo $view->render('admin::dashboard.index');
```

## Middleware Setup

```php
class AdminController extends Controller
{
    protected array $middleware = [
        AuthMiddleware::class,
    ];

    protected array $actionMiddleware = [
        'deleteAction' => [
            [RoleMiddleware::class, ['admin']],
        ],
    ];
}
```

## Related

- [MVC Routing](02-MVC-ROUTING.md)
- [HTTP Request](06-HTTP-REQUEST.md)
- [API Reference](11-API-REFERENCE.md)
