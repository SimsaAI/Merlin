# 🧩 Merlin\Mvc\Dispatcher

## 🔐 Properties

- `protected Merlin\AppContext $context`
- `protected 📦 array $globalMiddleware`
- `protected 📦 array $middlewareGroups`
- `protected 🔤 string $baseNamespace`
- `protected 🔤 string $defaultController`
- `protected 🔤 string $defaultAction`
- `protected 🎲 mixed $controllerFactory`

## 🚀 Public methods

### `__construct()`

`public function __construct() : mixed`

**➡️ Return value**

- Type: `mixed`

### `addMiddleware()`

`public function addMiddleware(Merlin\Mvc\MiddlewareInterface $mw) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$mw` | `Merlin\Mvc\MiddlewareInterface` | `` |  |

**➡️ Return value**

- Type: `void`

### `defineMiddlewareGroup()`

`public function defineMiddlewareGroup(string $name, array $middleware) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |
| `$middleware` | `📦 array` | `` |  |

**➡️ Return value**

- Type: `void`

### `getBaseNamespace()`

`public function getBaseNamespace() : string`

Get the base namespace for controllers.

**➡️ Return value**

- Type: `string`
- Description: The base namespace for controllers.

### `setBaseNamespace()`

`public function setBaseNamespace(string $baseNamespace) : static`

Set the base namespace for controllers. This namespace will be prefixed to all controller class names when dispatching.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$baseNamespace` | `🔤 string` | `` | The base namespace for controllers (e.g. "App\\Controllers") |

**➡️ Return value**

- Type: `static`

### `getDefaultController()`

`public function getDefaultController() : string`

Get the default controller name used when a route doesn't provide one.

**➡️ Return value**

- Type: `string`
- Description: Default controller class name (without namespace)

### `setDefaultController()`

`public function setDefaultController(string $defaultController) : static`

Set the default controller name.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultController` | `🔤 string` | `` | Controller class name to use as default |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \InvalidArgumentException If given name is empty

### `getDefaultAction()`

`public function getDefaultAction() : string`

Get the default action name used when a route doesn't provide one.

**➡️ Return value**

- Type: `string`
- Description: Default action method name

### `setDefaultAction()`

`public function setDefaultAction(string $defaultAction) : static`

Set the default action name.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | `🔤 string` | `` | Action method name to use as default |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \InvalidArgumentException If given name is empty

### `dispatch()`

`public function dispatch(array $routeInfo) : Merlin\Http\Response`

Dispatch a request to the appropriate controller and action based on the provided routing information. This method will determine the controller class and action method to invoke, build the middleware pipeline, and execute the controller action, returning the resulting Response.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$routeInfo` | `📦 array` | `` |  |

**➡️ Return value**

- Type: `Merlin\Http\Response`

**⚠️ Throws**

- \ControllerNotFoundException 
- \InvalidControllerException 
- \ActionNotFoundException 

### `setControllerFactory()`

`public function setControllerFactory(callable $factory) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$factory` | `callable` | `` |  |

**➡️ Return value**

- Type: `void`

