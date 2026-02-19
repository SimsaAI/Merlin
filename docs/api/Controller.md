# 🧩 Merlin\Mvc\Controller

MVC Controller class

## 🔐 Properties

- `protected 📦 array $middleware`
- `protected 📦 array $actionMiddleware`

## 🚀 Public methods

### `beforeAction()`

`public function beforeAction(string|null $action = null, array $params = []) : Merlin\Http\Response|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$action` | `string\|null` | `null` |  |
| `$params` | `📦 array` | `[]` |  |

**➡️ Return value**

- Type: `Merlin\Http\Response|null`

### `afterAction()`

`public function afterAction(string|null $action = null, array $params = []) : Merlin\Http\Response|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$action` | `string\|null` | `null` |  |
| `$params` | `📦 array` | `[]` |  |

**➡️ Return value**

- Type: `Merlin\Http\Response|null`

### `getMiddleware()`

`public function getMiddleware() : array`

**➡️ Return value**

- Type: `array`

### `getActionMiddleware()`

`public function getActionMiddleware(string $action) : array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$action` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `array`

