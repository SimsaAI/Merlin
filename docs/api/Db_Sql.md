# 🧩 Sql

**Full name:** [Merlin\Db\Sql](../../src/Db/Sql.php)

SQL Value Object - Tagged Union for SQL Expressions

Represents SQL expressions (functions, casts, arrays, etc.) that serialize at SQL generation time.
Default behavior: serialize to literals (debug-friendly)
Sql::param() creates bind parameters explicitly

## 🚀 Public methods

### column() · [source](../../src/Db/Sql.php#L79)

`public static function column(string $name): static`

Column reference (unquoted identifier)
Supports Model.column syntax for automatic table resolution

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Column name (simple or Model.column format) |

**➡️ Return value**

- Type: static


---

### param() · [source](../../src/Db/Sql.php#L94)

`public static function param(string $name): static`

Bind parameter reference

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Parameter name (without colons) |

**➡️ Return value**

- Type: static


---

### func() · [source](../../src/Db/Sql.php#L105)

`public static function func(string $name, array $args = []): static`

SQL function call

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Function name |
| `$args` | array | `[]` | Function arguments (scalars or Sql instances) |

**➡️ Return value**

- Type: static


---

### cast() · [source](../../src/Db/Sql.php#L116)

`public static function cast(mixed $value, string $type): static`

Type cast (driver-specific syntax)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - | Value to cast (scalar or Sql) |
| `$type` | string | - | Target type name |

**➡️ Return value**

- Type: static


---

### pgArray() · [source](../../src/Db/Sql.php#L126)

`public static function pgArray(array $values): static`

PostgreSQL array literal

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$values` | array | - | Array elements (scalars or Sql instances) |

**➡️ Return value**

- Type: static


---

### csList() · [source](../../src/Db/Sql.php#L136)

`public static function csList(array $values): static`

Comma-separated list (for IN clauses)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$values` | array | - | List elements (scalars or Sql instances) |

**➡️ Return value**

- Type: static


---

### raw() · [source](../../src/Db/Sql.php#L147)

`public static function raw(string $sql, array $bindParams = []): static`

Raw SQL (unescaped, passed through as-is)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$sql` | string | - | Raw SQL string |
| `$bindParams` | array | `[]` | Optional bind parameters ['param_name' => value] |

**➡️ Return value**

- Type: static


---

### value() · [source](../../src/Db/Sql.php#L159)

`public static function value(mixed $value): static`

Literal value (will be properly quoted/escaped)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - | Value to serialize as SQL literal |

**➡️ Return value**

- Type: static


---

### json() · [source](../../src/Db/Sql.php#L169)

`public static function json(mixed $value): static`

JSON value (serialized as JSON literal)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - | Value to encode as JSON |

**➡️ Return value**

- Type: static


---

### concat() · [source](../../src/Db/Sql.php#L181)

`public static function concat(mixed ...$parts): static`

Driver-aware string concatenation
PostgreSQL/SQLite: uses || operator
MySQL: uses CONCAT() function

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$parts` | mixed | - | Parts to concatenate (scalars or Sql instances) |

**➡️ Return value**

- Type: static


---

### expr() · [source](../../src/Db/Sql.php#L193)

`public static function expr(mixed ...$parts): static`

Composite expression - concatenates parts with spaces
Useful for complex expressions like CASE WHEN
Plain strings are treated as raw SQL tokens (not serialized)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$parts` | mixed | - | Expression parts (strings are raw, use Sql instances for values) |

**➡️ Return value**

- Type: static


---

### case() · [source](../../src/Db/Sql.php#L202)

`public static function case(): Merlin\Db\SqlCase`

CASE expression builder

**➡️ Return value**

- Type: [SqlCase](Db_SqlCase.md)
- Description: Fluent builder for CASE expressions


---

### subQuery() · [source](../../src/Db/Sql.php#L212)

`public static function subQuery(Merlin\Db\Query $query): static`

Subquery expression - wraps a Query instance as a subquery

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$query` | [Query](Db_Query.md) | - | Subquery instance |

**➡️ Return value**

- Type: static


---

### as() · [source](../../src/Db/Sql.php#L222)

`public function as(string $alias): static`

Add alias to this expression (returns aliased node)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$alias` | string | - | Column alias |

**➡️ Return value**

- Type: static


---

### getBindParams() · [source](../../src/Db/Sql.php#L232)

`public function getBindParams(): array`

Get bind parameters associated with this node

**➡️ Return value**

- Type: array
- Description: Associative array of bind parameters


---

### toSql() · [source](../../src/Db/Sql.php#L294)

`public function toSql(string $driver, callable $serialize, callable|null $protectIdentifier = null): string`

Serialize node to SQL string

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$driver` | string | - | Database driver (mysql, pgsql, sqlite) |
| `$serialize` | callable | - | Callback for serializing scalar values<br>Signature: fn(mixed $value, bool $param = false): string |
| `$protectIdentifier` | callable\|null | `null` | Callback for identifier resolution and quoting<br>Signature: fn(string $identifier, ?string $alias = null, int $mode = 0): string<br>If not provided, falls back to simple driver-based quoting |

**➡️ Return value**

- Type: string
- Description: SQL fragment


---

### __toString() · [source](../../src/Db/Sql.php#L422)

`public function __toString(): string`

**➡️ Return value**

- Type: string



---

[Back to the Index ⤴](index.md)
