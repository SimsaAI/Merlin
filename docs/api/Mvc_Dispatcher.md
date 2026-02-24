# 🧩 Class: Dispatcher

**Full name:** [Merlin\Mvc\Dispatcher](../../src/Mvc/Dispatcher.php)

## 🚀 Public methods

### __construct() · [source](../../src/Mvc/Dispatcher.php#L21)

`public function __construct(): mixed`

Create a new Dispatcher and bind it to the current {@see AppContext} singleton.

**➡️ Return value**

- Type: mixed


---

### addMiddleware() · [source](../../src/Mvc/Dispatcher.php#L37)

`public function addMiddleware(Merlin\Mvc\MiddlewareInterface $mw): void`

Register a middleware that runs on every dispatched request.

Global middleware is prepended to the pipeline before any group,
controller, or action middleware.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$mw` | [MiddlewareInterface](Mvc_MiddlewareInterface.md) | - | Middleware instance to add. |

**➡️ Return value**

- Type: void


---

### defineMiddlewareGroup() · [source](../../src/Mvc/Dispatcher.php#L54)

`public function defineMiddlewareGroup(string $name, array $middleware): void`

Define a named middleware group that can be referenced from route definitions.

Groups are applied after global middleware and before controller/action
middleware. If several middleware groups are active for a route, they are
applied in the order they are listed on the route.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Unique group name (e.g. "auth", "admin"). |
| `$middleware` | array | - | Array of middleware definitions accepted by the pipeline normalizer. |

**➡️ Return value**

- Type: void


---

### getBaseNamespace() · [source](../../src/Mvc/Dispatcher.php#L68)

`public function getBaseNamespace(): string`

Get the base namespace for controllers.

**➡️ Return value**

- Type: string
- Description: The base namespace for controllers.


---

### setBaseNamespace() · [source](../../src/Mvc/Dispatcher.php#L79)

`public function setBaseNamespace(string $baseNamespace): static`

Set the base namespace for controllers. This namespace will be prefixed to all controller class names when dispatching.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$baseNamespace` | string | - | The base namespace for controllers (e.g. "App\\Controllers") |

**➡️ Return value**

- Type: static


---

### getDefaultController() · [source](../../src/Mvc/Dispatcher.php#L90)

`public function getDefaultController(): string`

Get the default controller name used when a route doesn't provide one.

**➡️ Return value**

- Type: string
- Description: Default controller class name (without namespace)


---

### setDefaultController() · [source](../../src/Mvc/Dispatcher.php#L101)

`public function setDefaultController(string $defaultController): static`

Set the default controller name.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultController` | string | - | Controller class name to use as default |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- InvalidArgumentException  If given name is empty


---

### getDefaultAction() · [source](../../src/Mvc/Dispatcher.php#L115)

`public function getDefaultAction(): string`

Get the default action name used when a route doesn't provide one.

**➡️ Return value**

- Type: string
- Description: Default action method name


---

### setDefaultAction() · [source](../../src/Mvc/Dispatcher.php#L126)

`public function setDefaultAction(string $defaultAction): static`

Set the default action name.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | string | - | Action method name to use as default |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- InvalidArgumentException  If given name is empty


---

### dispatch() · [source](../../src/Mvc/Dispatcher.php#L143)

`public function dispatch(array $routeInfo): Merlin\Http\Response`

Dispatch a request to the appropriate controller and action based on the provided routing information. This method will determine the controller class and action method to invoke, build the middleware pipeline, and execute the controller action, returning the resulting Response.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$routeInfo` | array | - |  |

**➡️ Return value**

- Type: [Response](Http_Response.md)

**⚠️ Throws**

- [ControllerNotFoundException](Mvc_Exceptions_ControllerNotFoundException.md)
- [InvalidControllerException](Mvc_Exceptions_InvalidControllerException.md)
- [ActionNotFoundException](Mvc_Exceptions_ActionNotFoundException.md)



---

[Back to the Index ⤴](index.md)
