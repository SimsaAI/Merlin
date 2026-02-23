# 🧩 Class: ModelMapping

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

### usePluralTableNames() · [source](../../src/Mvc/ModelMapping.php#L56)

`public static function usePluralTableNames(bool $enable): void`

Enable or disable automatic table name pluralization.

When enabled, model names are converted to plural snake_case table names
(e.g. User → users, AdminUser → admin_users, Person → people).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$enable` | bool | - |  |

**➡️ Return value**

- Type: void


---

### usingPluralTableNames() · [source](../../src/Mvc/ModelMapping.php#L64)

`public static function usingPluralTableNames(): bool`

Returns whether automatic table name pluralization is enabled.

**➡️ Return value**

- Type: bool


---

### add() · [source](../../src/Mvc/ModelMapping.php#L76)

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

### get() · [source](../../src/Mvc/ModelMapping.php#L96)

`public function get(string $name): array|null`

Get model mapping by name

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: array|null


---

### toArray() · [source](../../src/Mvc/ModelMapping.php#L106)

`public function toArray(): array`

Get all model mappings as an array

**➡️ Return value**

- Type: array


---

### convertModelToSource() · [source](../../src/Mvc/ModelMapping.php#L119)

`public static function convertModelToSource(string $modelName): string`

Convert a model name to a default source name (table name).

By default, converts PascalCase or camelCase to snake_case (e.g. AdminUser → admin_user).
When pluralization is enabled, the last word segment is pluralized (e.g. AdminUser → admin_users).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$modelName` | string | - | The model class name to convert. |

**➡️ Return value**

- Type: string
- Description: The converted source name (table name).


---

### toSnakeCase() · [source](../../src/Mvc/ModelMapping.php#L149)

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

### pluralize() · [source](../../src/Mvc/ModelMapping.php#L258)

`public static function pluralize(string $word): string`

Return the plural form of a word (always lowercase).

Returns the word unchanged if it appears to be already plural.
Irregular plurals are applied first; regular suffix rules are used otherwise.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$word` | string | - | Singular word. |

**➡️ Return value**

- Type: string
- Description: Pluralized lowercase word.



---

[Back to the Index ⤴](index.md)
