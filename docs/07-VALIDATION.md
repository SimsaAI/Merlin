# Validation

Merlin includes a fluent input validation component that validates and coerces associative arrays (such as form POST data or decoded JSON) against a set of declarative field rules.

## Quick Start

```php
use Merlin\Validation\Validator;
use Merlin\Validation\ValidationException;

$v = new Validator($request->post());

$v->field('name')->required()->string()->min(2)->max(100);
$v->field('email')->required()->email()->max(255);
$v->field('age')->required()->int()->min(18)->max(120);
$v->field('bio')->optional()->string()->max(500);

if ($v->fails()) {
    return Response::json(['errors' => $v->errors()], 422);
}

$data = $v->validated(); // only validated and coerced fields
User::create($data);
```

Or throw on failure instead of branching:

```php
try {
    $data = $v->validate(); // throws ValidationException on failure
} catch (ValidationException $e) {
    return Response::json(['errors' => $e->errors()], 422);
}
```

## Registering Fields

Call `field(string $name)` to register a field and receive a fluent `FieldValidator`. Fields are **required by default**.

```php
$v->field('title');                        // required
$v->field('bio')->optional();              // missing key is silently ignored
$v->field('role')->default('viewer');      // missing key uses 'viewer'; implied optional
$v->field('count')->int()->default(0);
```

`default()` only applies when the field is **absent** from the input. When the key is present, the supplied value is validated and coerced normally. The default is included in `validated()` as-is — no rules are applied to it — so make sure it already matches the expected type.

`default()` implicitly makes a field optional. Calling `->optional()` alongside is allowed but redundant.

## Type Coercion

Type rules coerce the raw string value to the target PHP type. If coercion is not possible, the field fails immediately and subsequent rules for that field are skipped.

| Rule         | Accepts                                         | PHP type produced |
| ------------ | ----------------------------------------------- | ----------------- |
| `->int()`    | Integer strings, negative, native `int`         | `int`             |
| `->float()`  | Any numeric string, native `int`/`float`        | `float`           |
| `->bool()`   | `true`/`false`, `yes`/`no`, `on`/`off`, `1`/`0` | `bool`            |
| `->string()` | Anything castable (explicit cast)               | `string`          |

Without a type rule, the value is passed through as-is (HTTP input normally arrives as `string`; JSON input retains its decoded type).

```php
$v->field('count')->int();          // '42' → 42
$v->field('price')->float();        // '9.99' → 9.99
$v->field('active')->bool();        // 'yes' → true
$v->field('slug')->string();        // 123 → '123'
$v->field('meta')->optional();      // passed through unchanged
```

## Constraints

`min()` and `max()` apply to the value _after_ type coercion. Their meaning depends on type:

| Value type      | `min` / `max` measures         |
| --------------- | ------------------------------ |
| `string`        | Character length (`mb_strlen`) |
| `int` / `float` | Numeric value                  |
| `array`         | Item count (`count`)           |

```php
$v->field('username')->string()->min(3)->max(30);
$v->field('rating')->int()->min(1)->max(5);
$v->field('tags')->list(fn($f) => $f->string())->min(1)->max(10);
```

## Format Rules

Format rules validate the _content_ of the value without changing its type.

| Rule                       | Validates                              |
| -------------------------- | -------------------------------------- |
| `->email()`                | RFC 5321 e-mail address                |
| `->url()`                  | URL (`FILTER_VALIDATE_URL`)            |
| `->ip()`                   | IPv4 or IPv6 address                   |
| `->pattern(string $regex)` | Value matches the regular expression   |
| `->in(array $allowed)`     | Strict (`===`) membership in the array |

```php
$v->field('email')->email();
$v->field('website')->optional()->url();
$v->field('client_ip')->ip();
$v->field('zip')->pattern('/^\d{5}$/');
$v->field('role')->in(['admin', 'editor', 'viewer']);
```

## Structure Rules

### list()

Validates every element of an array against a sub-validator.

```php
// Each tag must be a non-empty string up to 50 characters
$v->field('tags')->optional()->list(fn($f) => $f->string()->min(1)->max(50));

// Each ID must be a positive integer
$v->field('ids')->list(fn($f) => $f->int()->min(1));
```

### model()

Validates a nested associative array. Each key maps to a callable that receives and configures a `FieldValidator`.

```php
$v->field('address')->required()->model([
    'street' => fn($f) => $f->required()->string()->max(200),
    'city'   => fn($f) => $f->required()->string()->max(100),
    'zip'    => fn($f) => $f->required()->pattern('/^\d{5}$/'),
    'state'  => fn($f) => $f->optional()->string()->max(50),
]);
```

`list` and `model` can be nested to any depth.

## Running Validation

| Method        | Return type             | Behaviour                                                       |
| ------------- | ----------------------- | --------------------------------------------------------------- |
| `fails()`     | `bool`                  | Runs rules, returns `true` if any field failed                  |
| `errors()`    | `array<string, string>` | Returns all error messages, keyed by dot-path field name        |
| `validated()` | `array<string, mixed>`  | Fields that passed, with coerced values; failed fields excluded |
| `validate()`  | `array<string, mixed>`  | Like `validated()` but throws `ValidationException` on failure  |

## Error Format

Errors are keyed by a dot-path derived from field nesting:

```
name          → top-level field
address.zip   → sub-field inside a model()
ids[2]        → third element inside a list()
items[0].qty  → sub-field inside a list of models
```

A typical JSON error response:

```json
{
  "errors": {
    "email": "must be a valid email address",
    "address.zip": "has an invalid format",
    "ids[1]": "must be an integer"
  }
}
```

## Controller Integration

### Branch on failure

```php
class UserController extends Controller
{
    public function createAction(): array
    {
        $v = new Validator($this->request()->post());
        $v->field('name')->string()->min(2)->max(100);
        $v->field('email')->email()->max(255);
        $v->field('role')->in(['admin', 'editor', 'viewer']);

        if ($v->fails()) {
            return ['success' => false, 'errors' => $v->errors()];
        }

        $user = User::create($v->validated());
        return ['success' => true, 'id' => $user->id];
    }
}
```

### Throw in a helper method

```php
class UserController extends Controller
{
    public function createAction(): Response
    {
        try {
            $data = $this->validateCreate($this->request()->post());
        } catch (ValidationException $e) {
            return Response::json(['errors' => $e->errors()], 422);
        }

        $user = User::create($data);
        return Response::json(['id' => $user->id], 201);
    }

    private function validateCreate(array $input): array
    {
        $v = new Validator($input);
        $v->field('name')->string()->min(2)->max(100);
        $v->field('email')->email()->max(255);
        return $v->validate(); // throws on failure
    }
}
```

## Related

- [Controllers & Views](03-CONTROLLERS-VIEWS.md)
- [HTTP Request](06-HTTP-REQUEST.md)
- [Security](09-SECURITY.md)
