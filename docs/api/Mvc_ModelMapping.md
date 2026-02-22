# 🧩 ModelMapping

**Full name:** [Merlin\Mvc\ModelMapping](../../src/Mvc/ModelMapping.php)

Class to map models

## 🚀 Public methods

### fromArray() · [source](../../src/Mvc/ModelMapping.php#L21)

`public static function fromArray(array $mapping): static`

Create ModelMapping from array config

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$mapping` | array | - |  |

**➡️ Return value**

- Type: static


---

### add() · [source](../../src/Mvc/ModelMapping.php#L55)

`public function add(string $name, string|null $source = null, string|null $schema = null): static`

Add model mapping

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |
| `$source` | string\|null | `null` |  |
| `$schema` | string\|null | `null` |  |

**➡️ Return value**

- Type: static


---

### get() · [source](../../src/Mvc/ModelMapping.php#L76)

`public function get(string $name): array|null`

Get model mapping by name

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: array|null


---

### toArray() · [source](../../src/Mvc/ModelMapping.php#L86)

`public function toArray(): array`

Get all model mappings as an array

**➡️ Return value**

- Type: array


---

### toSnakeCase() · [source](../../src/Mvc/ModelMapping.php#L100)

`public static function toSnakeCase(string $name): string`

Convert a string to snake_case.

Handles various input formats, including camelCase, PascalCase, kebab-case, and space-separated words.
Consecutive uppercase letters are treated as acronyms (e.g., XMLParser → xml_parser).
Multiple separators are unified into a single underscore, and duplicate underscores are avoided.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | The input string to convert. |

**➡️ Return value**

- Type: string
- Description: The converted snake_case string.



---

[Back to the Index ⤴](index.md)
