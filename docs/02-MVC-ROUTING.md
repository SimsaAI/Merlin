# MVC Routing

**Map URLs to controllers** - Master Merlin's routing system to define URL patterns, handle parameters, group routes, and apply middleware. Learn how to create RESTful routes, named routes, and custom parameter validation.

`Merlin\Mvc\Router` matches URI + HTTP method to controller/action metadata.
`Merlin\Mvc\Dispatcher` executes that route and returns a `Response`.

## Basic Usage

```php
use Merlin\Mvc\Dispatcher;
use Merlin\Mvc\Router;

$router = new Router();
$router->add('GET', '/', 'IndexController::indexAction');
$router->add('GET', '/users/{id:int}', 'UserController::viewAction');

// Configure dispatcher
$dispatcher = new Dispatcher();
$dispatcher->setBaseNamespace('App\\Controllers');

$route = $router->match('/users/42', 'GET');
if ($route !== null) {
    $response = $dispatcher->dispatch($route);
    $response->send();
}
```

## Route Patterns

The Router supports several pattern styles to match different URL structures. You can use static paths for exact matches, named parameters to capture URL segments, and type constraints to validate parameter formats.

### Static

Static routes match exact paths and are the fastest to resolve:

```php
$router->add('GET', '/about', 'PageController::aboutAction');
```

### Named Parameters

Capture dynamic segments from the URL as parameters passed to your controller action:

```php
$router->add('GET', '/blog/{slug}', 'BlogController::showAction');
```

### Typed Parameters

Add type constraints to validate parameters automatically. This helps prevent invalid data from reaching your controllers and makes routes more self-documenting.

Built-in types: `int`, `alpha`, `alnum`, `uuid`, `*`.

```php
$router->add('GET', '/users/{id:int}', 'UserController::viewAction');
$router->add('GET', '/tags/{name:alpha}', 'TagController::showAction');
```

### Routing Variables

Certain parameter names have special meaning to the Dispatcher and control how controllers and actions are resolved:

```php
$router->add('GET', '/{controller}/{action}');
$router->add('GET', '/api/{namespace}/{controller}/{action}/{params:*}');
```

Routing variables recognized by the Dispatcher:

- `{namespace}` - Appends to the base namespace for controller resolution
- `{controller}` - Specifies the controller name (converted to PascalCase + 'Controller')
- `{action}` - Specifies the action method name (converted to camelCase + 'Action')
- `{params:*}` - Captures all remaining path segments as an array

Example: `/api/admin/user/view/123` with pattern `/api/{namespace}/{controller}/{action}/{params:*}` yields:

- `namespace` → `Admin` (appended to base namespace)
- `controller` → `UserController`
- `action` → `viewAction`
- `params` → `['123']`

**Note:** When you specify a handler (third parameter to `add()`), it overrides the routing variables. This lets you use parameters named `controller`, `action`, etc. for other purposes:

```php
// Uses routing variables to resolve controller/action dynamically
$router->add('GET', '/{controller}/{action}');

// Handler overrides - 'controller' becomes a regular parameter
$router->add('GET', '/admin/{controller}', 'AdminController::manageAction');
```

## HTTP Methods

Routes can be restricted to specific HTTP methods for proper RESTful API design. You can specify a single method, an array of methods, or `*` or null to match all common methods (GET, POST, PUT, DELETE, PATCH, OPTIONS).

```php
$router->add('GET', '/users', 'UserController::listAction');
$router->add(['PUT', 'PATCH'], '/users/{id:int}', 'UserController::updateAction');
$router->add('*', '/health', 'HealthController::statusAction');
```

## Named Routes and URL Generation

Named routes let you generate URLs programmatically without hardcoding paths. This makes refactoring routes easier and keeps your codebase maintainable.

```php
$router->add('GET', '/users/{id:int}', 'UserController::viewAction')
    ->setName('user.view');

$url = $router->urlFor('user.view', ['id' => 42], ['tab' => 'profile']);
// /users/42?tab=profile
```

## Custom Parameter Types

Define your own validation rules for route parameters. This is useful for application-specific formats like slugs, SKUs, or reference codes.

```php
$router->addType('slug', fn(string $v) => mb_ereg('^[a-z0-9-]+$', $v));
$router->add('GET', '/posts/{slug:slug}', 'PostController::showAction');
```

## Prefix and Middleware Groups

Organize related routes with common prefixes or middleware. This reduces repetition and makes route structure clearer.

```php
$router->prefix('/admin', function (Router $r) {
    $r->add('GET', '/users', 'AdminController::usersAction');
});

$router->middleware('auth', function (Router $r) {
    $r->add('GET', '/dashboard', 'DashboardController::indexAction');
});
```

Route group middleware names are attached to route info and consumed by `Dispatcher` middleware groups.

## Configuration

### Router Options

```php
$router = new Router();
$router->setParseParams(true); // Auto-parse params to int/float/bool/null
```

### Dispatcher Configuration

The Dispatcher handles controller resolution and default values:

```php
$dispatcher = new Dispatcher();
$dispatcher->setBaseNamespace('App\\Controllers'); // Default: 'App\\Controllers'
$dispatcher->setDefaultController('IndexController'); // Default: 'IndexController'
$dispatcher->setDefaultAction('indexAction'); // Default: 'indexAction'
```

## Route Information in AppContext

When the Dispatcher processes a route, it stores the routing information in `AppContext->routing` as a `RoutingResult` object. This makes the current route accessible throughout your application:

```php
// In any controller, middleware, or service:
$routing = AppContext::instance()->routing;

// Access route details:
$controller = $routing->controller; // Full controller class name
$action = $routing->action; // Action method name
$namespace = $routing->namespace; // Resolved namespace
$vars = $routing->vars; // All route variables
$params = $routing->params; // Parameters passed to action
$groups = $routing->groups; // Middleware groups
$override = $routing->override; // Handler overrides
```

## Important Notes

- Router focuses purely on pattern matching - no namespace or defaults
- Dispatcher handles controller resolution, defaults, and namespace logic
- Use `Router::match(...)` then `Dispatcher::dispatch(...)`
- Route info is stored in `AppContext->routing` during dispatch

## Dispatcher Argument Resolution

`Dispatcher` keeps route vars and resolved action arguments separate:

- `vars['params']`: raw wildcard route payload from the router
- `RoutingResult->params`: effective arguments used to call the action method

`RoutingResult->params` is built from:

1. all `vars` except routing keys (`controller`, `action`, `namespace`, `params`)
2. then values from `vars['params']`

This means `RoutingResult->params` is the effective call payload for controller actions and
for `beforeAction(..., $params)` / `afterAction(..., $params)` hooks.

Example:

```php
$routeInfo = [
    'vars' => [
        'id' => 42,
        'controller' => 'routing-state',
        'action' => 'from-override',
        'params' => ['x', 'y'],
    ],
    'override' => [
        'namespace' => 'Merlin\\Tests\\Mvc',
        'controller' => 'RoutingStateController',
        'action' => 'fromOverride',
    ],
];

$dispatcher->dispatch($routeInfo);

// Stored in AppContext->routing:
// vars['params'] => ['x', 'y']
// params         => [42, 'x', 'y']
```

## See Also

- [Controllers & Views](03-CONTROLLERS-VIEWS.md)
- [API Reference](11-API-REFERENCE.md)
