# 🧩 Class: Condition

**Full name:** [Azera\Db\Condition](../../src/Db/Condition.php)

Build conditions for WHERE, HAVING, ON etc. clauses

Usage examples:

// Simple condition
$c = Condition::create()->where('id', 123);

// Qualified identifiers (automatically quoted)
$c = Condition::create()->where('users.status', 'active');

// Large IN lists (no regex issues)
$c = Condition::create()->inWhere('id', range(1, 10000));

// JOIN conditions
$joinCond = Condition::create()->where('o.user_id = u.id');
$sb->leftJoin('orders o', $joinCond);

// Complex conditions
$c = Condition::create()
    ->where('u.age', 18, '>=')
    ->where('u.status', 'active')
    ->group(
        fn(Condition $g) =>
           $g->where('u.role', 'admin')
               ->orWhere('u.role', 'moderator')
    );

## 🚀 Public methods

### new() · [source](../../src/Db/Condition.php#L89)

`public static function new(Azera\Db\Database|null $db = null): static`

Create a new Condition builder instance

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | [Database](Db_Database.md)\|null | `null` |  |

**➡️ Return value**

- Type: static


---

### __construct() · [source](../../src/Db/Condition.php#L98)

`public function __construct(Azera\Db\Database|null $db = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | [Database](Db_Database.md)\|null | `null` |  |

**➡️ Return value**

- Type: mixed

**⚠️ Throws**

- Exception


---

### injectModelResolver() · [source](../../src/Db/Condition.php#L143)

`public function injectModelResolver(callable $resolver): void`

Inject model resolver from Query builder

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$resolver` | callable | - | Callable that takes model name and returns table name |

**➡️ Return value**

- Type: void


---

### where() · [source](../../src/Db/Condition.php#L194)

`public function where(Azera\Db\Condition|string $conditionOrField, mixed $valueOrOp = null, mixed $escapeOrValue = true): static`

Appends a condition to the current conditions using an AND operator.

Three forms:
  where('col', $value)               equality (value escaped inline)
  where('col', '=', $value)          explicit operator — BOUND param
  where('age >=', 18)                operator embedded (legacy CI style)

Supported operators in the explicit form: =, !=, <>, <, <=, >, >=,
LIKE, NOT LIKE, IN, NOT IN. Values always go through bound
parameters (never interpolation); IN () over an empty list compiles
to the semantically correct 1=0 / 1=1.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditionOrField` | [Condition](Db_Condition.md)\|string | - |  |
| `$valueOrOp` | mixed | `null` | Value (CI style), or the operator token in the<br>explicit operator form |
| `$escapeOrValue` | mixed | `true` | Escape flag (CI style) — or the actual<br>value in the explicit operator form |

**➡️ Return value**

- Type: static


---

### orWhere() · [source](../../src/Db/Condition.php#L206)

`public function orWhere(Azera\Db\Condition|string $conditionOrField, mixed $valueOrOp = null, mixed $escapeOrValue = true): static`

Appends a condition to the current conditions using a OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditionOrField` | [Condition](Db_Condition.md)\|string | - |  |
| `$valueOrOp` | mixed | `null` |  |
| `$escapeOrValue` | mixed | `true` |  |

**➡️ Return value**

- Type: static


---

### notWhere() · [source](../../src/Db/Condition.php#L218)

`public function notWhere(Azera\Db\Condition|string $conditionOrField, mixed $valueOrOp = null, mixed $escapeOrValue = true): static`

Appends a negated condition to the current conditions using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditionOrField` | [Condition](Db_Condition.md)\|string | - |  |
| `$valueOrOp` | mixed | `null` |  |
| `$escapeOrValue` | mixed | `true` |  |

**➡️ Return value**

- Type: static


---

### orNotWhere() · [source](../../src/Db/Condition.php#L230)

`public function orNotWhere(Azera\Db\Condition|string $conditionOrField, mixed $valueOrOp = null, mixed $escapeOrValue = true): static`

Appends a negated condition to the current conditions using an OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditionOrField` | [Condition](Db_Condition.md)\|string | - |  |
| `$valueOrOp` | mixed | `null` |  |
| `$escapeOrValue` | mixed | `true` |  |

**➡️ Return value**

- Type: static


---

### betweenWhere() · [source](../../src/Db/Condition.php#L404)

`public function betweenWhere(string $condition, mixed $minimum, mixed $maximum): static`

Appends a BETWEEN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$minimum` | mixed | - |  |
| `$maximum` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### notBetweenWhere() · [source](../../src/Db/Condition.php#L416)

`public function notBetweenWhere(string $condition, mixed $minimum, mixed $maximum): static`

Appends a NOT BETWEEN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$minimum` | mixed | - |  |
| `$maximum` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### orBetweenWhere() · [source](../../src/Db/Condition.php#L428)

`public function orBetweenWhere(string $condition, mixed $minimum, mixed $maximum): static`

Appends a BETWEEN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$minimum` | mixed | - |  |
| `$maximum` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### orNotBetweenWhere() · [source](../../src/Db/Condition.php#L440)

`public function orNotBetweenWhere(string $condition, mixed $minimum, mixed $maximum): static`

Appends a NOT BETWEEN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$minimum` | mixed | - |  |
| `$maximum` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### inWhere() · [source](../../src/Db/Condition.php#L475)

`public function inWhere(string $condition, mixed $values): static`

Appends an IN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$values` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### notInWhere() · [source](../../src/Db/Condition.php#L486)

`public function notInWhere(string $condition, mixed $values): static`

Appends an NOT IN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$values` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### orInWhere() · [source](../../src/Db/Condition.php#L497)

`public function orInWhere(string $condition, mixed $values): static`

Appends an IN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$values` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### orNotInWhere() · [source](../../src/Db/Condition.php#L508)

`public function orNotInWhere(string $condition, mixed $values): static`

Appends an NOT IN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | string | - |  |
| `$values` | mixed | - |  |

**➡️ Return value**

- Type: static


---

### having() · [source](../../src/Db/Condition.php#L548)

`public function having(Azera\Db\Sql|string $condition, mixed $values = null): static`

Appends an HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### notHaving() · [source](../../src/Db/Condition.php#L559)

`public function notHaving(Azera\Db\Sql|string $condition, mixed $values = null): static`

Appends an NOT HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### orHaving() · [source](../../src/Db/Condition.php#L570)

`public function orHaving(Azera\Db\Sql|string $condition, mixed $values = null): static`

Appends an HAVING condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### orNotHaving() · [source](../../src/Db/Condition.php#L580)

`public function orNotHaving(Azera\Db\Sql|string $condition, mixed $values = null): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### likeWhere() · [source](../../src/Db/Condition.php#L618)

`public function likeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a LIKE condition to the current condition

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### andLikeWhere() · [source](../../src/Db/Condition.php#L631)

`public function andLikeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a LIKE condition to the current condition using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### orLikeWhere() · [source](../../src/Db/Condition.php#L644)

`public function orLikeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a LIKE condition to the current condition using an OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### notLikeWhere() · [source](../../src/Db/Condition.php#L657)

`public function notLikeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a NOT LIKE condition to the current condition

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### andNotLikeWhere() · [source](../../src/Db/Condition.php#L670)

`public function andNotLikeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a NOT LIKE condition to the current condition using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### orNotLikeWhere() · [source](../../src/Db/Condition.php#L683)

`public function orNotLikeWhere(string $identifier, mixed $value, bool $escape = true): static`

Appends a NOT LIKE condition to the current condition using an OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | string | - |  |
| `$value` | mixed | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### group() · [source](../../src/Db/Condition.php#L732)

`public function group(callable $callback): static`

Build a grouped condition using a callback.

The callback receives a fresh Condition builder whose contents are
wrapped in parentheses and appended to the current builder using AND.
Bindings and deferred model prefixes are merged into the parent.

Example:
  $c->group(function (Condition $g) {
      $g->where('role', 'admin')
        ->orWhere('role', 'moderator');
  });

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$callback` | callable | - |  |

**➡️ Return value**

- Type: static


---

### orGroup() · [source](../../src/Db/Condition.php#L744)

`public function orGroup(callable $callback): static`

Build a grouped condition using a callback, joined with OR.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$callback` | callable | - |  |

**➡️ Return value**

- Type: static


---

### notGroup() · [source](../../src/Db/Condition.php#L756)

`public function notGroup(callable $callback): static`

Build a negated grouped condition using a callback, joined with AND.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$callback` | callable | - |  |

**➡️ Return value**

- Type: static


---

### orNotGroup() · [source](../../src/Db/Condition.php#L768)

`public function orNotGroup(callable $callback): static`

Build a negated grouped condition using a callback, joined with OR.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$callback` | callable | - |  |

**➡️ Return value**

- Type: static


---

### noop() · [source](../../src/Db/Condition.php#L802)

`public function noop(): static`

No operator function. Useful to build flexible chains

**➡️ Return value**

- Type: static


---

### bind() · [source](../../src/Db/Condition.php#L1214)

`public function bind(array $bindParams): static`

Replace placeholders in the condition with actual values

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$bindParams` | array | - |  |

**➡️ Return value**

- Type: static


---

### toSql() · [source](../../src/Db/Condition.php#L1227)

`public function toSql(): string`

Get the condition

**➡️ Return value**

- Type: string


---

### getBindings() · [source](../../src/Db/Condition.php#L1236)

`public function getBindings(): array`

Get bind parameters

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](README.md)
