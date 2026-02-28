# 🧩 Class: Validator

**Full name:** [Merlin\Validation\Validator](../../src/Validation/Validator.php)

Validates and coerces an associative input array against a set of field rules.

Usage:

  $v = new Validator($request->post());

  $v->field('email')->required()->email()->max(255);
  $v->field('age')->required()->int()->min(18)->max(120);
  $v->field('name')->optional()->string()->min(2)->max(100);
  $v->field('tags')->optional()->list(fn($f) => $f->string()->max(50));
  $v->field('address')->optional()->model([
      'street' => fn($f) => $f->required()->string(),
      'zip'    => fn($f) => $f->required()->pattern('/^\d{5}$/'),
  ]);

  if ($v->fails()) {
      return Response::json(['errors' => $v->errors()], 422);
  }
  $data = $v->validated();

Or in a single call (throws ValidationException on failure):

  $data = $v->validate();

## 🚀 Public methods

### __construct() · [source](../../src/Validation/Validator.php#L46)

`public function __construct(array $data): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | array | - | Raw input array (e.g. from Request::post()). |

**➡️ Return value**

- Type: mixed


---

### field() · [source](../../src/Validation/Validator.php#L56)

`public function field(string $name): Merlin\Validation\FieldValidator`

Register rules for a field and return the fluent FieldValidator.

Fields default to required. Call ->optional() on the returned validator
to make the field optional.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: [FieldValidator](Validation_FieldValidator.md)


---

### fails() · [source](../../src/Validation/Validator.php#L67)

`public function fails(): bool`

Run all rules. Returns true when at least one rule failed.

**➡️ Return value**

- Type: bool


---

### errors() · [source](../../src/Validation/Validator.php#L79)

`public function errors(): array`

Dot-path keyed error messages from the last run.

Empty when validation has not been run yet or all rules passed.

**➡️ Return value**

- Type: array


---

### validated() · [source](../../src/Validation/Validator.php#L91)

`public function validated(): array`

Returns only the fields that passed validation, with values coerced to
their declared types. Fields that failed are excluded.

**➡️ Return value**

- Type: array


---

### validate() · [source](../../src/Validation/Validator.php#L103)

`public function validate(): array`

Run validation and return the validated data, or throw on failure.

**➡️ Return value**

- Type: array

**⚠️ Throws**

- [ValidationException](Validation_ValidationException.md)



---

[Back to the Index ⤴](index.md)
