# 🧩 Class: ValidationException

**Full name:** [Merlin\Validation\ValidationException](../../src/Validation/ValidationException.php)

Thrown by Validator::validate() when one or more field rules fail.

The errors array is keyed by dot-path field name (e.g. "address.zip", "tags[0]")
and each value is a human-readable error message string.

## 🚀 Public methods

### __construct() · [source](../../src/Validation/ValidationException.php#L16)

`public function __construct(array $errors): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$errors` | array | - | Dot-path field errors. |

**➡️ Return value**

- Type: mixed


---

### errors() · [source](../../src/Validation/ValidationException.php#L24)

`public function errors(): array`

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](index.md)
