# 🧩 Controller

**Full name:** [Merlin\Mvc\Controller](../../src/Mvc/Controller.php)

MVC Controller class

## 🔐 Properties

- `protected` array `$middleware` · [source](../../src/Mvc/Controller.php)
- `protected` array `$actionMiddleware` · [source](../../src/Mvc/Controller.php)

## 🚀 Public methods

### beforeAction() · [source](../../src/Mvc/Controller.php#L37)

`public function beforeAction(string|null $action = null, array $params = []): Merlin\Http\Response|null`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$action` | string\|null | `null` |  |
| `$params` | array | `[]` |  |

**➡️ Return value**

- Type: [Response](Response.md)|null

### afterAction() · [source](../../src/Mvc/Controller.php#L42)

`public function afterAction(string|null $action = null, array $params = []): Merlin\Http\Response|null`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$action` | string\|null | `null` |  |
| `$params` | array | `[]` |  |

**➡️ Return value**

- Type: [Response](Response.md)|null

### getMiddleware() · [source](../../src/Mvc/Controller.php#L49)

`public function getMiddleware(): array`

**➡️ Return value**

- Type: array

### getActionMiddleware() · [source](../../src/Mvc/Controller.php#L54)

`public function getActionMiddleware(string $action): array`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$action` | string | - |  |

**➡️ Return value**

- Type: array

