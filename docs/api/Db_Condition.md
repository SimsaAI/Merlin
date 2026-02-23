# 🧩 Class: Condition

**Full name:** [Merlin\Db\Condition](../../src/Db/Condition.php)

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
    ->andWhere('u.status', 'active')
    ->groupStart()
        ->where('u.role', 'admin')
        ->orWhere('u.role', 'moderator')
    ->groupEnd();

## 🚀 Public methods

### new() · [source](../../src/Db/Condition.php#L91)

`public static function new(Merlin\Db\Database|null $db = null): static`

Create a new Condition builder instance

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | [Database](Db_Database.md)\|null | `null` |  |

**➡️ Return value**

- Type: static


---

### __construct() · [source](../../src/Db/Condition.php#L100)

`public function __construct(Merlin\Db\Database|null $db = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | [Database](Db_Database.md)\|null | `null` |  |

**➡️ Return value**

- Type: mixed

**⚠️ Throws**

- Exception


---

### injectModelResolver() · [source](../../src/Db/Condition.php#L147)

`public function injectModelResolver(callable $resolver): void`

Inject model resolver from Query builder

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$resolver` | callable | - | Callable that takes model name and returns table name |

**➡️ Return value**

- Type: void


---

### where() · [source](../../src/Db/Condition.php#L187)

`public function where(Merlin\Db\Condition|string $condition, mixed $value = null, bool $escape = true): static`

Appends a condition to the current conditions using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Condition](Db_Condition.md)\|string | - |  |
| `$value` | mixed | `null` |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### orWhere() · [source](../../src/Db/Condition.php#L199)

`public function orWhere(Merlin\Db\Condition|string $condition, mixed $value = null, bool $escape = true): static`

Appends a condition to the current conditions using a OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Condition](Db_Condition.md)\|string | - |  |
| `$value` | mixed | `null` |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static


---

### betweenWhere() · [source](../../src/Db/Condition.php#L277)

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

### notBetweenWhere() · [source](../../src/Db/Condition.php#L289)

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

### orBetweenWhere() · [source](../../src/Db/Condition.php#L301)

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

### orNotBetweenWhere() · [source](../../src/Db/Condition.php#L313)

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

### inWhere() · [source](../../src/Db/Condition.php#L348)

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

### notInWhere() · [source](../../src/Db/Condition.php#L359)

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

### orInWhere() · [source](../../src/Db/Condition.php#L370)

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

### orNotInWhere() · [source](../../src/Db/Condition.php#L381)

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

### having() · [source](../../src/Db/Condition.php#L422)

`public function having(Merlin\Db\Sql|string $condition, mixed $values = null): static`

Appends an HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### notHaving() · [source](../../src/Db/Condition.php#L433)

`public function notHaving(Merlin\Db\Sql|string $condition, mixed $values = null): static`

Appends an NOT HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### orHaving() · [source](../../src/Db/Condition.php#L444)

`public function orHaving(Merlin\Db\Sql|string $condition, mixed $values = null): static`

Appends an HAVING condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### orNotHaving() · [source](../../src/Db/Condition.php#L454)

`public function orNotHaving(Merlin\Db\Sql|string $condition, mixed $values = null): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | [Sql](Db_Sql.md)\|string | - |  |
| `$values` | mixed | `null` |  |

**➡️ Return value**

- Type: static


---

### likeWhere() · [source](../../src/Db/Condition.php#L492)

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

### andLikeWhere() · [source](../../src/Db/Condition.php#L505)

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

### orLikeWhere() · [source](../../src/Db/Condition.php#L518)

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

### notLikeWhere() · [source](../../src/Db/Condition.php#L531)

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

### andNotLikeWhere() · [source](../../src/Db/Condition.php#L544)

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

### orNotLikeWhere() · [source](../../src/Db/Condition.php#L557)

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

### groupStart() · [source](../../src/Db/Condition.php#L594)

`public function groupStart(): static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query.

**➡️ Return value**

- Type: static


---

### orGroupStart() · [source](../../src/Db/Condition.php#L608)

`public function orGroupStart(): static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR’.

**➡️ Return value**

- Type: static


---

### notGroupStart() · [source](../../src/Db/Condition.php#L622)

`public function notGroupStart(): static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘NOT’.

**➡️ Return value**

- Type: static


---

### orNotGroupStart() · [source](../../src/Db/Condition.php#L636)

`public function orNotGroupStart(): static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR NOT’.

**➡️ Return value**

- Type: static


---

### groupEnd() · [source](../../src/Db/Condition.php#L650)

`public function groupEnd(): static`

Ends the current group by adding an closing parenthesis to the WHERE clause of the query.

**➡️ Return value**

- Type: static


---

### noop() · [source](../../src/Db/Condition.php#L661)

`public function noop(): static`

No operator function. Useful to build flexible chains

**➡️ Return value**

- Type: static


---

### bind() · [source](../../src/Db/Condition.php#L970)

`public function bind(array $bindParams): static`

Replace placeholders in the condition with actual values

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$bindParams` | array | - |  |

**➡️ Return value**

- Type: static


---

### toSql() · [source](../../src/Db/Condition.php#L983)

`public function toSql(): string`

Get the condition

**➡️ Return value**

- Type: string


---

### getBindings() · [source](../../src/Db/Condition.php#L992)

`public function getBindings(): array`

Get bind parameters

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](index.md)
