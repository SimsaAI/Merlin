# 🧩 Merlin\Db\Condition

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

## 📌 Constants

- **PI_DEFAULT** = `0`
- **PI_COLUMN** = `1`
- **PI_TABLE** = `2`

## 🔐 Properties

- `protected Merlin\Db\Database|null $db`
- `protected 🔤 string $condition`
- `protected ⚙️ bool $needOperator`
- `protected 🔢 int $paramCounter`
- `protected 📦 array $autoBindParams`
- `protected 🎲 mixed $modelResolver`
- `protected 📦 array $tableCache`
- `protected 📦 array $deferredModelPrefixes`
- `protected string|null $finalCondition`

## 🚀 Public methods

### `new()`

`public static function new(Merlin\Db\Database|null $db = null) : static`

Create a new Condition builder instance

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | `Merlin\Db\Database\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

### `__construct()`

`public function __construct(Merlin\Db\Database|null $db = null) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | `Merlin\Db\Database\|null` | `null` |  |

**➡️ Return value**

- Type: `mixed`

**⚠️ Throws**

- \Exception 

### `injectModelResolver()`

`public function injectModelResolver(callable $resolver) : void`

Inject model resolver from Query builder

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$resolver` | `callable` | `` | Callable that takes model name and returns table name |

**➡️ Return value**

- Type: `void`

### `where()`

`public function where(Merlin\Db\Condition|string $condition, $value = null, bool $escape = true) : static`

Appends a condition to the current conditions using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Condition\|string` | `` |  |
| `$value` | `🎲 mixed` | `null` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `orWhere()`

`public function orWhere(Merlin\Db\Condition|string $condition, $value = null, bool $escape = true) : static`

Appends a condition to the current conditions using a OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Condition\|string` | `` |  |
| `$value` | `🎲 mixed` | `null` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `betweenWhere()`

`public function betweenWhere(string $condition, $minimum, $maximum) : static`

Appends a BETWEEN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$minimum` | `🎲 mixed` | `` |  |
| `$maximum` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `notBetweenWhere()`

`public function notBetweenWhere(string $condition, $minimum, $maximum) : static`

Appends a NOT BETWEEN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$minimum` | `🎲 mixed` | `` |  |
| `$maximum` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `orBetweenWhere()`

`public function orBetweenWhere(string $condition, $minimum, $maximum) : static`

Appends a BETWEEN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$minimum` | `🎲 mixed` | `` |  |
| `$maximum` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `orNotBetweenWhere()`

`public function orNotBetweenWhere(string $condition, $minimum, $maximum) : static`

Appends a NOT BETWEEN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$minimum` | `🎲 mixed` | `` |  |
| `$maximum` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `inWhere()`

`public function inWhere(string $condition, $values) : static`

Appends an IN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$values` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `notInWhere()`

`public function notInWhere(string $condition, $values) : static`

Appends an NOT IN condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$values` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `orInWhere()`

`public function orInWhere(string $condition, $values) : static`

Appends an IN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$values` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `orNotInWhere()`

`public function orNotInWhere(string $condition, $values) : static`

Appends an NOT IN condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🔤 string` | `` |  |
| `$values` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `having()`

`public function having(Merlin\Db\Sql|string $condition, $values = null) : static`

Appends an HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Sql\|string` | `` |  |
| `$values` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `static`

### `notHaving()`

`public function notHaving(Merlin\Db\Sql|string $condition, $values = null) : static`

Appends an NOT HAVING condition to the current conditions using AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Sql\|string` | `` |  |
| `$values` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `static`

### `orHaving()`

`public function orHaving(Merlin\Db\Sql|string $condition, $values = null) : static`

Appends an HAVING condition to the current conditions using OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Sql\|string` | `` |  |
| `$values` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `static`

### `orNotHaving()`

`public function orNotHaving(Merlin\Db\Sql|string $condition, $values = null) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `Merlin\Db\Sql\|string` | `` |  |
| `$values` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `static`

### `likeWhere()`

`public function likeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a LIKE condition to the current condition

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `andLikeWhere()`

`public function andLikeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a LIKE condition to the current condition using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `orLikeWhere()`

`public function orLikeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a LIKE condition to the current condition using an OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `notLikeWhere()`

`public function notLikeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a NOT LIKE condition to the current condition

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `andNotLikeWhere()`

`public function andNotLikeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a NOT LIKE condition to the current condition using an AND operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `orNotLikeWhere()`

`public function orNotLikeWhere(string $identifier, $value, bool $escape = true) : static`

Appends a NOT LIKE condition to the current condition using an OR operator

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$identifier` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `groupStart()`

`public function groupStart() : static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query.

**➡️ Return value**

- Type: `static`

### `orGroupStart()`

`public function orGroupStart() : static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR’.

**➡️ Return value**

- Type: `static`

### `notGroupStart()`

`public function notGroupStart() : static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘NOT’.

**➡️ Return value**

- Type: `static`

### `orNotGroupStart()`

`public function orNotGroupStart() : static`

Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR NOT’.

**➡️ Return value**

- Type: `static`

### `groupEnd()`

`public function groupEnd() : static`

Ends the current group by adding an closing parenthesis to the WHERE clause of the query.

**➡️ Return value**

- Type: `static`

### `noop()`

`public function noop() : static`

No operator function. Useful to build flexible chains

**➡️ Return value**

- Type: `static`

### `bind()`

`public function bind(array $bindParams) : static`

Replace placeholders in the condition with actual values

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$bindParams` | `📦 array` | `` |  |

**➡️ Return value**

- Type: `static`

### `toSql()`

`public function toSql() : string`

Get the condition

**➡️ Return value**

- Type: `string`

