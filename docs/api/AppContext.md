# 🧩 Merlin\AppContext

## 🔐 Properties

- `protected 📦 array $services`
- `protected Merlin\Http\Request|null $request`
- `protected Merlin\Mvc\ViewEngine|null $view`
- `protected Merlin\Http\Session|null $session`
- `protected Merlin\Http\Cookies|null $cookies`
- `protected Merlin\ResolvedRoute|null $route`
- `protected Merlin\Db\DatabaseManager $dbManager`
- `protected static Merlin\AppContext|null $instance`

## 🚀 Public methods

### `__construct()`

`public function __construct() : mixed`

**➡️ Return value**

- Type: `mixed`

### `instance()`

`public static function instance() : static`

Get the singleton instance of AppContext. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: `static`
- Description: The singleton instance of AppContext.

### `setInstance()`

`public static function setInstance(Merlin\AppContext $instance) : void`

Set the singleton instance of AppContext. This can be used to inject a custom context, for example in tests.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$instance` | `Merlin\AppContext` | `` | The AppContext instance to set as the singleton. |

**➡️ Return value**

- Type: `void`

### `request()`

`public function request() : Merlin\Http\Request`

Get the HttpRequest instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: `Merlin\Http\Request`
- Description: The HttpRequest instance.

### `view()`

`public function view() : Merlin\Mvc\ViewEngine`

Get the ViewEngine instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: `Merlin\Mvc\ViewEngine`
- Description: The ViewEngine instance.

### `cookies()`

`public function cookies() : Merlin\Http\Cookies`

Get the Cookies instance. If it doesn't exist, it will be created.

**➡️ Return value**

- Type: `Merlin\Http\Cookies`
- Description: The Cookies instance.

### `dbManager()`

`public function dbManager() : Merlin\Db\DatabaseManager`

**➡️ Return value**

- Type: `Merlin\Db\DatabaseManager`

### `session()`

`public function session() : Merlin\Http\Session|null`

Get the Session instance.

**➡️ Return value**

- Type: `Merlin\Http\Session|null`

### `route()`

`public function route() : Merlin\ResolvedRoute|null`

Get the current resolved route information.

**➡️ Return value**

- Type: `Merlin\ResolvedRoute|null`

### `setRoute()`

`public function setRoute(Merlin\ResolvedRoute $route) : void`

Set the current resolved route information.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$route` | `Merlin\ResolvedRoute` | `` | The resolved route to set in the context. |

**➡️ Return value**

- Type: `void`

### `set()`

`public function set(string $id, object $service) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🔤 string` | `` |  |
| `$service` | `🧱 object` | `` |  |

**➡️ Return value**

- Type: `void`

### `has()`

`public function has(string $id) : bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `bool`

### `get()`

`public function get(string $id) : object`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `object`

### `tryGet()`

`public function tryGet(string $id) : object|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `object|null`

### `getOrNull()`

`public function getOrNull(string $id) : object|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `object|null`

