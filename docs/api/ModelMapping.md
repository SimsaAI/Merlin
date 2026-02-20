# 🧩 ModelMapping

**Full name:** [Merlin\Mvc\ModelMapping](../../src/Mvc/ModelMapping.php)

Class to map models

## 🔐 Properties

- `private` array `$mapping` · [source](../../src/Mvc/ModelMapping.php)

## 🚀 Public methods

### __construct() · [source](../../src/Mvc/ModelMapping.php#L19)

`public function __construct(array|null $mapping = null): mixed`

ModelMapping constructor.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$mapping` | array\|null | `null` |  |

**➡️ Return value**

- Type: mixed

### add() · [source](../../src/Mvc/ModelMapping.php#L31)

`public function add(string $name, string|null $source = null, string|null $schema = null): static`

Add model mapping

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | string | - |  |
| `$source` | string\|null | `null` |  |
| `$schema` | string\|null | `null` |  |

**➡️ Return value**

- Type: static

### get() · [source](../../src/Mvc/ModelMapping.php#L52)

`public function get(string $name): array|null`

Get model mapping by name

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: array|null

### getAll() · [source](../../src/Mvc/ModelMapping.php#L62)

`public function getAll(): array`

Get all model mapping

**➡️ Return value**

- Type: array

### toSnakeCase() · [source](../../src/Mvc/ModelMapping.php#L76)

`public static function toSnakeCase(string $name): string`

Convert a string to snake_case.

Handles various input formats, including camelCase, PascalCase, kebab-case, and space-separated words.
Consecutive uppercase letters are treated as acronyms (e.g., XMLParser → xml_parser).
Multiple separators are unified into a single underscore, and duplicate underscores are avoided.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | string | - | The input string to convert. |

**➡️ Return value**

- Type: string
- Description: The converted snake_case string.

