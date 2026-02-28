# 🧩 Class: FieldValidator

**Full name:** [Merlin\Validation\FieldValidator](../../src/Validation/FieldValidator.php)

Fluent validator for a single input field.

Chain rules to describe what the field must look like.
The validator is executed by [`Validator`](Validation_Validator.md) (or the nested model/list machinery)
via the internal `validate()` method.

Example:
  $v->field('email')->required()->email()->max(255);
  $v->field('age')->optional()->int()->min(18)->max(120);
  $v->field('tags')->optional()->list(fn($f) => $f->string()->max(50));

## 🚀 Public methods

### required() · [source](../../src/Validation/FieldValidator.php#L39)

`public function required(): static`

**➡️ Return value**

- Type: static


---

### optional() · [source](../../src/Validation/FieldValidator.php#L45)

`public function optional(): static`

**➡️ Return value**

- Type: static


---

### isRequired() · [source](../../src/Validation/FieldValidator.php#L51)

`public function isRequired(): bool`

**➡️ Return value**

- Type: bool


---

### default() · [source](../../src/Validation/FieldValidator.php#L61)

`public function default(mixed $value): static`

Supply a default value used when the field is absent.

Calling default() implicitly makes the field optional.
The default is included in validated() as-is (no rules are applied to it).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### hasDefault() · [source](../../src/Validation/FieldValidator.php#L69)

`public function hasDefault(): bool`

**➡️ Return value**

- Type: bool


---

### getDefault() · [source](../../src/Validation/FieldValidator.php#L74)

`public function getDefault(): mixed`

**➡️ Return value**

- Type: mixed


---

### int() · [source](../../src/Validation/FieldValidator.php#L84)

`public function int(): static`

Coerce to integer. Accepts int values and numeric strings (including negatives).

**➡️ Return value**

- Type: static


---

### float() · [source](../../src/Validation/FieldValidator.php#L93)

`public function float(): static`

Coerce to float. Accepts any numeric value.

**➡️ Return value**

- Type: static


---

### bool() · [source](../../src/Validation/FieldValidator.php#L102)

`public function bool(): static`

Coerce to bool. Accepts true/false, 1/0, "true"/"false", "yes"/"no", "on"/"off".

**➡️ Return value**

- Type: static


---

### string() · [source](../../src/Validation/FieldValidator.php#L111)

`public function string(): static`

Explicitly cast to string. Useful for ensuring min/max applies to character length.

**➡️ Return value**

- Type: static


---

### min() · [source](../../src/Validation/FieldValidator.php#L125)

`public function min(int|float $n): static`

Minimum value / length / count depending on type:
  - string: minimum character length (mb_strlen)
  - int/float: minimum numeric value
  - array: minimum number of items

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$n` | int\|float | - |  |

**➡️ Return value**

- Type: static


---

### max() · [source](../../src/Validation/FieldValidator.php#L134)

`public function max(int|float $n): static`

Maximum value / length / count (same semantics as min).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$n` | int\|float | - |  |

**➡️ Return value**

- Type: static


---

### email() · [source](../../src/Validation/FieldValidator.php#L143)

`public function email(): static`

Value must be a valid e-mail address (RFC 5321).

**➡️ Return value**

- Type: static


---

### url() · [source](../../src/Validation/FieldValidator.php#L150)

`public function url(): static`

Value must be a valid URL (FILTER_VALIDATE_URL).

**➡️ Return value**

- Type: static


---

### ip() · [source](../../src/Validation/FieldValidator.php#L157)

`public function ip(): static`

Value must be a valid IPv4 or IPv6 address.

**➡️ Return value**

- Type: static


---

### pattern() · [source](../../src/Validation/FieldValidator.php#L164)

`public function pattern(string $regex): static`

Value must match the given regular expression.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$regex` | string | - |  |

**➡️ Return value**

- Type: static


---

### in() · [source](../../src/Validation/FieldValidator.php#L175)

`public function in(array $allowed): static`

Value must be strictly equal (===) to one of the allowed values.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$allowed` | array | - |  |

**➡️ Return value**

- Type: static


---

### list() · [source](../../src/Validation/FieldValidator.php#L188)

`public function list(callable $configure): static`

Value must be an array; each element is validated by the configured sub-validator.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$configure` | callable | - |  |

**➡️ Return value**

- Type: static


---

### model() · [source](../../src/Validation/FieldValidator.php#L202)

`public function model(array $fields): static`

Value must be an associative array matching the given field definitions.

Each entry maps a key name to a callable that configures a FieldValidator.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fields` | array | - |  |

**➡️ Return value**

- Type: static


---

### validate() · [source](../../src/Validation/FieldValidator.php#L218)

`public function validate(mixed $value, string $path, array &$errors): mixed`

Apply all configured rules to $value, appending any errors to $errors.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - | The raw input value. |
| `$path` | string | - | Dot-path used as the error key. |
| `$errors` | array | - | Accumulated errors (mutated in place). |

**➡️ Return value**

- Type: mixed
- Description: The coerced / validated value.



---

[Back to the Index ⤴](index.md)
