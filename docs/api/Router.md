# 🧩 Merlin\Mvc\Router

## 🔐 Properties

- `protected 📦 array $static`
- `protected 📦 array $groups`
- `protected 📦 array $types`
- `protected 📦 array $middlewareGroupStack`
- `protected 📦 array $prefixGroupStack`
- `protected 📦 array $namedRoutes`
- `protected array|null $lastAddedTokens`

## 🚀 Public methods

### `__construct()`

`public function __construct() : mixed`

Create a new Router instance.

**➡️ Return value**

- Type: `mixed`

### `addType()`

`public function addType(string $name, callable $validator) : static`

Register a custom type validator for route parameters.

Predefined types include 'int', 'alpha', 'alnum', 'uuid', and '*' (matches anything). You can add your own types with custom validation logic. For example, you could add a 'slug' type that only allows lowercase letters, numbers, and hyphens. Once a type is registered, you can use it in your route patterns like /blog/{slug:slug}.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | The type name (e.g., 'slug', 'email') |
| `$validator` | `callable` | `` | Function that validates a string value, returns bool |

**➡️ Return value**

- Type: `static`
- Description: For method chaining

### `add()`

`public function add(array|string|null $method, string $pattern, array|string|null $handler = null) : static`

Add a new route to the router. The route can be defined for specific HTTP methods, a URI pattern, and an optional handler that overrides the default controller/action resolution. The pattern can include static segments, typed parameters, dynamic segments for namespace/controller/action, and wildcard segments for additional parameters. Validators can be applied to dynamic parameters using predefined or custom types. For example: /user/{id:int} or /blog/{slug:slug}

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$method` | `array\|string\|null` | `` | HTTP method(s) for the route (e.g., 'GET', ['GET', 'POST'], or null for all methods) |
| `$pattern` | `🔤 string` | `` | Route pattern (e.g., '/blog/{slug}', '/{:controller}/{:action}/{:params}') |
| `$handler` | `array\|string\|null` | `null` | Optional handler definition to override controller/action. Can be a string like 'Admin::dashboard' or an array with keys 'namespace', 'controller', 'action'. |

**➡️ Return value**

- Type: `static`
- Description: For method chaining

### `setName()`

`public function setName(string $name) : static`

Assign a name to the most recently added route. This allows you to generate URLs for this route using the `urlFor()` method.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | The name to assign to the route |

**➡️ Return value**

- Type: `static`
- Description: For method chaining

**⚠️ Throws**

- \LogicException If no route has been added yet or if the last added route is invalid

### `hasNamedRoute()`

`public function hasNamedRoute(string $name) : bool`

Check if a named route exists.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | The name of the route to check |

**➡️ Return value**

- Type: `bool`
- Description: True if a route with the given name exists, false otherwise

### `urlFor()`

`public function urlFor(string $name, array $params = [], array $query = []) : string`

Generate a URL for a named route, substituting parameters as needed.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | The name of the route to generate a URL for |
| `$params` | `📦 array` | `[]` | Associative array of parameter values to substitute into the route pattern |
| `$query` | `📦 array` | `[]` | Optional associative array of query parameters to append to the URL |

**➡️ Return value**

- Type: `string`
- Description: The generated URL path (e.g., "/blog/hello-world?ref=homepage")

**⚠️ Throws**

- \RuntimeException If no route with the given name exists or if required parameters are missing/invalid

### `prefix()`

`public function prefix(string $prefix, callable $callback) : void`

Define a group of routes that share a common URI prefix. This allows you to organize related routes together and avoid repeating the same prefix for each route. The callback function receives the router instance as an argument, allowing you to define routes within the group using the same `add()` method. The prefix is automatically prepended to all routes defined within the group. You can also nest groups within groups for more complex route hierarchies.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$prefix` | `🔤 string` | `` | URI prefix for the group (e.g., "/admin") |
| `$callback` | `callable` | `` | Function that receives the router instance to define routes within the group |

**➡️ Return value**

- Type: `void`

### `middleware()`

`public function middleware(array|string $name, callable $callback) : void`

Add group of middleware to be applied to all routes defined within the group. This allows you to easily apply common middleware (e.g., authentication, logging) to related routes without having to specify the middleware for each controller individually. The callback function receives the router instance as an argument, allowing you to define routes within the group using the same `add()` method. Middleware groups can be nested within other groups, and middleware from outer groups will be applied to inner groups as well.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `array\|string` | `` | Middleware group name (e.g., "auth") |
| `$callback` | `callable` | `` | Function that receives the router instance to define routes within the group |

**➡️ Return value**

- Type: `void`

### `match()`

`public function match(string $uri, string $method = 'GET') : array|null`

Attempt to match the given URI and HTTP method against the registered routes.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$uri` | `🔤 string` | `` | The request URI (path) to match, e.g. "/blog/hello-world" |
| `$method` | `🔤 string` | `'GET'` | The HTTP method, e.g. "GET", "POST" |

**➡️ Return value**

- Type: `array|null`
- Description: If a match is found, returns an array with keys 'vars', 'override', 'groups', 'wildcards'. Otherwise, returns null.

