# 🧩 ResolvedRoute

**Full name:** [Merlin\ResolvedRoute](../../src/AppContext.php)

Class ResolvedRoute

Represents the fully resolved route and execution context used by the
dispatcher to invoke the matched controller and action.

This includes:
- the effective namespace (after applying route group namespaces)
- the resolved controller class
- the resolved action method name
- the resolved action method parameters
- route variables extracted from the URL
- route middleware groups
- route overrides (e.g. controller/action)

## 🔐 Properties

- `public` 🔤 `string`|`null` `$namespace` · [source](../../src/AppContext.php)
- `public` 🔤 `string` `$controller` · [source](../../src/AppContext.php)
- `public` 🔤 `string` `$action` · [source](../../src/AppContext.php)
- `public` 📦 `array` `$params` · [source](../../src/AppContext.php)
- `public` 📦 `array` `$vars` · [source](../../src/AppContext.php)
- `public` 📦 `array` `$groups` · [source](../../src/AppContext.php)
- `public` 📦 `array` `$override` · [source](../../src/AppContext.php)

## 🚀 Public methods

### __construct() · [source](../../src/AppContext.php#L298)

`public function __construct(string|null $namespace, string $controller, string $action, array $params, array $vars, array $groups, array $override): mixed`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$namespace` | 🔤 `string`\|`null` | - |  |
| `$controller` | 🔤 `string` | - |  |
| `$action` | 🔤 `string` | - |  |
| `$params` | 📦 `array` | - |  |
| `$vars` | 📦 `array` | - |  |
| `$groups` | 📦 `array` | - |  |
| `$override` | 📦 `array` | - |  |

**➡️ Return value**

- Type: 🎲 `mixed`

