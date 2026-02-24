# 🧩 Class: AppContext

**Full name:** [Merlin\AppContext](../../src/AppContext.php)

## 🚀 Public methods

### __construct() · [source](../../src/AppContext.php#L14)

`public function __construct(): mixed`

**➡️ Return value**

- Type: mixed


---

### instance() · [source](../../src/AppContext.php#L57)

`public static function instance(): static`

Get the singleton instance of AppContext. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: static
- Description: The singleton instance of AppContext.


---

### setInstance() · [source](../../src/AppContext.php#L68)

`public static function setInstance(Merlin\AppContext $instance): void`

Set the singleton instance of AppContext. This can be used to inject a custom context, for example in tests.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$instance` | [AppContext](AppContext.md) | - | The AppContext instance to set as the singleton. |

**➡️ Return value**

- Type: void


---

### request() · [source](../../src/AppContext.php#L80)

`public function request(): Merlin\Http\Request`

Get the HttpRequest instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: [Request](Http_Request.md)
- Description: The HttpRequest instance.


---

### view() · [source](../../src/AppContext.php#L90)

`public function view(): Merlin\Mvc\ViewEngine`

Get the ViewEngine instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: [ViewEngine](Mvc_ViewEngine.md)
- Description: The ViewEngine instance.


---

### cookies() · [source](../../src/AppContext.php#L100)

`public function cookies(): Merlin\Http\Cookies`

Get the Cookies instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: [Cookies](Http_Cookies.md)
- Description: The Cookies instance.


---

### dbManager() · [source](../../src/AppContext.php#L111)

`public function dbManager(): Merlin\Db\DatabaseManager`

Get the DatabaseManager instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: [DatabaseManager](Db_DatabaseManager.md)
- Description: The DatabaseManager instance.


---

### router() · [source](../../src/AppContext.php#L121)

`public function router(): Merlin\Mvc\Router`

Get the Router instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: [Router](Mvc_Router.md)
- Description: The Router instance.


---

### session() · [source](../../src/AppContext.php#L131)

`public function session(): Merlin\Http\Session|null`

Get the Session instance.

**➡️ Return value**

- Type: [Session](Http_Session.md)|null


---

### setSession() · [source](../../src/AppContext.php#L141)

`public function setSession(Merlin\Http\Session $session): void`

Set the Session instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$session` | [Session](Http_Session.md) | - | The Session instance to set in the context. |

**➡️ Return value**

- Type: void


---

### route() · [source](../../src/AppContext.php#L149)

`public function route(): Merlin\ResolvedRoute|null`

Get the current resolved route information.

**➡️ Return value**

- Type: [ResolvedRoute](ResolvedRoute.md)|null


---

### setRoute() · [source](../../src/AppContext.php#L159)

`public function setRoute(Merlin\ResolvedRoute $route): void`

Set the current resolved route information.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$route` | [ResolvedRoute](ResolvedRoute.md) | - | The resolved route to set in the context. |

**➡️ Return value**

- Type: void


---

### set() · [source](../../src/AppContext.php#L172)

`public function set(string $id, object $service): void`

Register a service instance in the context.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | string | - | The identifier for the service (usually the class name). |
| `$service` | object | - | The service instance to register. |

**➡️ Return value**

- Type: void


---

### has() · [source](../../src/AppContext.php#L183)

`public function has(string $id): bool`

Check if a service is registered in the context.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | string | - | The identifier of the service to check. |

**➡️ Return value**

- Type: bool
- Description: True if the service is registered, false otherwise.


---

### get() · [source](../../src/AppContext.php#L195)

`public function get(string $id): object`

Get a service instance from the context. If the service is not registered but the identifier is a class name, it will attempt to auto-wire and instantiate it.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | string | - | The identifier of the service to retrieve. |

**➡️ Return value**

- Type: object
- Description: The service instance associated with the given identifier.

**⚠️ Throws**

- RuntimeException  If the service is not found and cannot be auto-wired.


---

### tryGet() · [source](../../src/AppContext.php#L214)

`public function tryGet(string $id): object|null`

Try to get a service instance from the context. If the service is not registered but the identifier is a class name, it will attempt to auto-wire and instantiate it. Returns null if the service is not found and cannot be auto-wired.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | string | - | The identifier of the service to retrieve. |

**➡️ Return value**

- Type: object|null
- Description: The service instance associated with the given identifier, or null if not found.


---

### getOrNull() · [source](../../src/AppContext.php#L233)

`public function getOrNull(string $id): object|null`

Get a service instance from the context if it exists, or null if it does not exist. This method does not attempt to auto-wire or instantiate classes.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | string | - | The identifier of the service to retrieve. |

**➡️ Return value**

- Type: object|null
- Description: The service instance associated with the given identifier, or null if not found.



---

[Back to the Index ⤴](index.md)
