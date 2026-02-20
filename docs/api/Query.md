# 🧩 Query

**Full name:** [Merlin\Db\Query](../../src/Db/Query.php)

Unified query builder for SELECT, INSERT, UPDATE, DELETE operations

## 📌 Constants

- **PI_DEFAULT** = `0`
- **PI_COLUMN** = `1`
- **PI_TABLE** = `2`

## 🔐 Properties

- `protected static` bool `$useModels` · [source](../../src/Db/Query.php)
- `protected static` array `$modelCache` · [source](../../src/Db/Query.php)
- `protected static` [ModelMapping](ModelMapping.md)|null `$modelMapping` · [source](../../src/Db/Query.php)
- `protected` [Model](Model.md)|null `$model` · [source](../../src/Db/Query.php)
- `protected` array `$bindParams` · [source](../../src/Db/Query.php)
- `protected` int `$limit` · [source](../../src/Db/Query.php)
- `protected` int `$offset` · [source](../../src/Db/Query.php)
- `protected` int `$rowCount` · [source](../../src/Db/Query.php)
- `protected` bool `$isReadQuery` · [source](../../src/Db/Query.php)
- `protected` bool `$hasResultSet` · [source](../../src/Db/Query.php)
- `protected` array|null `$columns` · [source](../../src/Db/Query.php)
- `protected` array `$joins` · [source](../../src/Db/Query.php)
- `protected` array `$orderBy` · [source](../../src/Db/Query.php)
- `protected` array `$values` · [source](../../src/Db/Query.php)
- `protected` bool `$getModelDb` · [source](../../src/Db/Query.php)
- `protected` string|null `$table` · [source](../../src/Db/Query.php)
- `protected` bool `$returnSql` · [source](../../src/Db/Query.php)
- `protected` array `$groupBy` · [source](../../src/Db/Query.php)
- `protected` bool `$forUpdate` · [source](../../src/Db/Query.php)
- `protected` bool `$sharedLock` · [source](../../src/Db/Query.php)
- `protected` bool `$distinct` · [source](../../src/Db/Query.php)
- `protected` string `$preColumnInjection` · [source](../../src/Db/Query.php)
- `protected` bool `$replaceInto` · [source](../../src/Db/Query.php)
- `protected` bool `$ignore` · [source](../../src/Db/Query.php)
- `protected` array `$updateValues` · [source](../../src/Db/Query.php)
- `protected` bool `$updateValuesIsList` · [source](../../src/Db/Query.php)
- `protected` array|string `$conflictTarget` · [source](../../src/Db/Query.php)
- `protected` array|string|null `$returning` · [source](../../src/Db/Query.php)
- `protected` [Database](Database.md)|null `$db` · [source](../../src/Db/Query.php)
- `protected` string `$condition` · [source](../../src/Db/Query.php)
- `protected` bool `$needOperator` · [source](../../src/Db/Query.php)
- `protected` int `$paramCounter` · [source](../../src/Db/Query.php)
- `protected` array `$autoBindParams` · [source](../../src/Db/Query.php)
- `protected` mixed `$modelResolver` · [source](../../src/Db/Query.php)
- `protected` array `$tableCache` · [source](../../src/Db/Query.php)
- `protected` array `$deferredModelPrefixes` · [source](../../src/Db/Query.php)
- `protected` string|null `$finalCondition` · [source](../../src/Db/Query.php)

## 🚀 Public methods

### useModels() · [source](../../src/Db/Query.php#L56)

`public static function useModels(bool $useModels): void`

Enable or disable automatic model resolution for queries. If enabled, the query will resolve table names and database connections from model classes. If disabled, the query will treat table names as literal and use database connections from AppContext. This can be useful for simple queries or when you want to avoid coupling to model classes.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$useModels` | bool | - |  |

**➡️ Return value**

- Type: void

### setModelMapping() · [source](../../src/Db/Query.php#L65)

`public static function setModelMapping(Merlin\Mvc\ModelMapping|null $modelMapping): void`

Set the model mapping instance to use for resolving model class names to table names and database connections. This can be used instead of model classes for simple queries or when you want to avoid coupling to model classes.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$modelMapping` | [ModelMapping](ModelMapping.md)\|null | - |  |

**➡️ Return value**

- Type: void

### __construct() · [source](../../src/Db/Query.php#L161)

`public function __construct(Merlin\Db\Database|null $db = null, Merlin\Mvc\Model|null $model = null): mixed`

Constructor. Can optionally pass a Database connection to use for this query, or a Model to automatically set the table and connection.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$db` | [Database](Database.md)\|null | `null` |  |
| `$model` | [Model](Model.md)\|null | `null` |  |

**➡️ Return value**

- Type: mixed

### new() · [source](../../src/Db/Query.php#L175)

`public static function new(Merlin\Db\Database|null $db = null): static`

Factory method to create a new Query instance. Can optionally pass a Database connection to use for this query.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$db` | [Database](Database.md)\|null | `null` |  |

**➡️ Return value**

- Type: static

### table() · [source](../../src/Db/Query.php#L213)

`public function table(string $name, string|null $alias = null): static`

Set the table for this query. Can be either a table name or a model class name. If a model class name is provided, the corresponding table will be used and the model's database connection will be used if no connection is set on the query.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | string | - | Table name or model class name |
| `$alias` | string\|null | `null` | Optional table alias |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### columns() · [source](../../src/Db/Query.php#L229)

`public function columns(array|string $columns): static`

Set columns for SELECT queries. Can be either a comma-separated string or an array of column names.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$columns` | array\|string | - |  |

**➡️ Return value**

- Type: static

### limit() · [source](../../src/Db/Query.php#L246)

`public function limit(int $limit, int $offset = 0): static`

Set the LIMIT and optional OFFSET for SELECT queries
(or limit number of rows affected for UPDATE/DELETE)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$limit` | int | - |  |
| `$offset` | int | `0` |  |

**➡️ Return value**

- Type: static

### offset() · [source](../../src/Db/Query.php#L258)

`public function offset(int $offset): static`

Sets an OFFSET clause for SELECT queries

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$offset` | int | - |  |

**➡️ Return value**

- Type: static

### values() · [source](../../src/Db/Query.php#L272)

`public function values(object|array $values, bool $escape = true): static`

Adds values for INSERT or UPDATE queries. Can be either:
- An associative array of column => value pairs
- An object with public properties

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$values` | object\|array | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static

### bulkValues() · [source](../../src/Db/Query.php#L296)

`public function bulkValues(array $valuesList = [], bool $escape = true): static`

Set multiple rows of values for bulk insert operations.

Each item in the list should be an array of column => value pairs.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$valuesList` | array | `[]` |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static

### hasValues() · [source](../../src/Db/Query.php#L316)

`public function hasValues(): bool`

Check if any values have been set for this query

**➡️ Return value**

- Type: bool

### set() · [source](../../src/Db/Query.php#L330)

`public function set(array|string $column, mixed $value = null, bool $escape = true): static`

Set a value for INSERT or UPDATE queries. Can be either:
- A single column name and value pair
- An associative array of column => value pairs

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$column` | array\|string | - |  |
| `$value` | mixed | `null` |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static

### join() · [source](../../src/Db/Query.php#L361)

`public function join(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null, string|null $type = null): static`

Add a JOIN clause to the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$model` | string | - |  |
| `$alias` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$conditions` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$type` | string\|null | `null` |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### innerJoin() · [source](../../src/Db/Query.php#L419)

`public function innerJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null): static`

Adds an INNER join to the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$model` | string | - |  |
| `$alias` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$conditions` | [Condition](Condition.md)\|string\|null | `null` |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### leftJoin() · [source](../../src/Db/Query.php#L432)

`public function leftJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null): static`

Adds a LEFT join to the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$model` | string | - |  |
| `$alias` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$conditions` | [Condition](Condition.md)\|string\|null | `null` |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### rightJoin() · [source](../../src/Db/Query.php#L445)

`public function rightJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null): static`

Adds a RIGHT join to the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$model` | string | - |  |
| `$alias` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$conditions` | [Condition](Condition.md)\|string\|null | `null` |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### crossJoin() · [source](../../src/Db/Query.php#L458)

`public function crossJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null): static`

Adds a CROSS join to the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$model` | string | - |  |
| `$alias` | [Condition](Condition.md)\|string\|null | `null` |  |
| `$conditions` | [Condition](Condition.md)\|string\|null | `null` |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### orderBy() · [source](../../src/Db/Query.php#L468)

`public function orderBy(array|string $orderBy): static`

Set ORDER BY clause

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$orderBy` | array\|string | - |  |

**➡️ Return value**

- Type: static

### bind() · [source](../../src/Db/Query.php#L481)

`public function bind(object|array $bindParams): static`

Bind parameters for prepared statements. Can be either an associative array or an object with properties as parameter names.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$bindParams` | object\|array | - |  |

**➡️ Return value**

- Type: static

### returnSql() · [source](../../src/Db/Query.php#L495)

`public function returnSql(bool $returnSql = true): static`

Set whether to return the SQL string instead of executing the query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$returnSql` | bool | `true` |  |

**➡️ Return value**

- Type: static

### distinct() · [source](../../src/Db/Query.php#L510)

`public function distinct(bool $distinct): static`

Set DISTINCT modifier for SELECT queries

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$distinct` | bool | - |  |

**➡️ Return value**

- Type: static

### injectBeforeColumns() · [source](../../src/Db/Query.php#L521)

`public function injectBeforeColumns(string $inject): static`

Set a string to be injected before the column list in SELECT queries (e.g. for SQL_CALC_FOUND_ROWS in MySQL)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$inject` | string | - |  |

**➡️ Return value**

- Type: static

### groupBy() · [source](../../src/Db/Query.php#L532)

`public function groupBy(array|string $groupBy): static`

Set GROUP BY clause

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$groupBy` | array\|string | - |  |

**➡️ Return value**

- Type: static

### forUpdate() · [source](../../src/Db/Query.php#L545)

`public function forUpdate(bool $forUpdate): static`

Sets a FOR UPDATE clause (MySQL/PostgreSQL) or FOR SHARE (PostgreSQL)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$forUpdate` | bool | - |  |

**➡️ Return value**

- Type: static

### sharedLock() · [source](../../src/Db/Query.php#L556)

`public function sharedLock(bool $sharedLock): static`

Sets a LOCK IN SHARE MODE / FOR SHARE clause (MySQL/PostgreSQL)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$sharedLock` | bool | - |  |

**➡️ Return value**

- Type: static

### replace() · [source](../../src/Db/Query.php#L571)

`public function replace(bool $replace = true): static`

Mark this as a REPLACE INTO operation (MySQL/SQLite)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$replace` | bool | `true` |  |

**➡️ Return value**

- Type: static

### ignore() · [source](../../src/Db/Query.php#L582)

`public function ignore(bool $ignore = true): static`

Set IGNORE modifier for INSERT (MySQL/SQLite) or ON CONFLICT DO NOTHING (PostgreSQL)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$ignore` | bool | `true` |  |

**➡️ Return value**

- Type: static

### updateValues() · [source](../../src/Db/Query.php#L596)

`public function updateValues(array $updateValues, bool $escape = true): static`

Set values for ON CONFLICT/ON DUPLICATE KEY UPDATE clause. Can be either:
- List array -> EXCLUDED/VALUES mode
- Assoc array -> explicit values

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$updateValues` | array | - |  |
| `$escape` | bool | `true` |  |

**➡️ Return value**

- Type: static

### conflict() · [source](../../src/Db/Query.php#L627)

`public function conflict(array|string $columnsOrConstraint): static`

Set conflict target for ON CONFLICT clause (PostgreSQL). Can be either:
- Array with column names
- String with column names or constraint name

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$columnsOrConstraint` | array\|string | - |  |

**➡️ Return value**

- Type: static

### returning() · [source](../../src/Db/Query.php#L639)

`public function returning(array|string|null $columns): static`

Set columns to return from an INSERT/UPDATE/DELETE query. Supported by PostgreSQL (RETURNING) and MySQL (RETURNING with MySQL 8.0.27+)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$columns` | array\|string\|null | - |  |

**➡️ Return value**

- Type: static

**⚠️ Throws**

- [Exception](Exception.md)

### toSql() · [source](../../src/Db/Query.php#L660)

`public function toSql(): string`

Compile and return the SQL string for this query without executing it

**➡️ Return value**

- Type: string

**⚠️ Throws**

- [Exception](Exception.md)

### select() · [source](../../src/Db/Query.php#L672)

`public function select(array|string|null $columns = null): Merlin\Db\ResultSet|string`

Execute SELECT query and return ResultSet or return SQL string if returnSql is enabled

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$columns` | array\|string\|null | `null` | Columns to select, or null to ignore parameter. Can be either a comma-separated string or an array of column names. |

**➡️ Return value**

- Type: [ResultSet](ResultSet.md)|string

**⚠️ Throws**

- [Exception](Exception.md)

### first() · [source](../../src/Db/Query.php#L697)

`public function first(): Merlin\Mvc\Model|string|null`

Execute SELECT query and return first model or null or return SQL string if returnSql is enabled

**➡️ Return value**

- Type: [Model](Model.md)|string|null
- Description: First model, or SQL string, or null if no results

**⚠️ Throws**

- [Exception](Exception.md)

### insert() · [source](../../src/Db/Query.php#L712)

`public function insert(array|null $data = null): Merlin\Db\ResultSet|array|string|bool`

Execute INSERT or UPSERT query or return SQL string if returnSql is enabled

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$data` | array\|null | `null` | Data to insert |

**➡️ Return value**

- Type: [ResultSet](ResultSet.md)|array|string|bool
- Description: Insert ID, true on success, or SQL string, or result of returning clause

**⚠️ Throws**

- [Exception](Exception.md)

### upsert() · [source](../../src/Db/Query.php#L723)

`public function upsert(array|null $data = null): Merlin\Db\ResultSet|array|string|bool`

Execute UPSERT query (INSERT with ON CONFLICT/ON DUPLICATE KEY UPDATE) or return SQL string if returnSql is enabled

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$data` | array\|null | `null` | Data to insert |

**➡️ Return value**

- Type: [ResultSet](ResultSet.md)|array|string|bool
- Description: Insert ID, true on success, or SQL string, or result of returning clause

**⚠️ Throws**

- [Exception](Exception.md)

### update() · [source](../../src/Db/Query.php#L764)

`public function update(array|null $data = null): Merlin\Db\ResultSet|array|string|int`

Execute UPDATE query or return SQL string if returnSql is enabled

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$data` | array\|null | `null` | Data to update |

**➡️ Return value**

- Type: [ResultSet](ResultSet.md)|array|string|int
- Description: Number of affected rows or SQL string, or row of returning clause

**⚠️ Throws**

- [Exception](Exception.md)

### delete() · [source](../../src/Db/Query.php#L792)

`public function delete(): Merlin\Db\ResultSet|array|string|int`

Execute DELETE query

**➡️ Return value**

- Type: [ResultSet](ResultSet.md)|array|string|int
- Description: Number of affected rows, SQL string, or result of returning clause

**⚠️ Throws**

- [Exception](Exception.md)

### truncate() · [source](../../src/Db/Query.php#L815)

`public function truncate(): string|int`

Execute TRUNCATE query or return SQL string if returnSql is enabled

**➡️ Return value**

- Type: string|int
- Description: Number of affected rows or SQL string

**⚠️ Throws**

- [Exception](Exception.md)

### exists() · [source](../../src/Db/Query.php#L834)

`public function exists(): string|bool`

Check if any rows exist matching the query

**➡️ Return value**

- Type: string|bool

**⚠️ Throws**

- [Exception](Exception.md)

### count() · [source](../../src/Db/Query.php#L858)

`public function count(): string|int`

Count rows matching the query

**➡️ Return value**

- Type: string|int
- Description: Number of matching rows or SQL string

**⚠️ Throws**

- [Exception](Exception.md)

### getBindings() · [source](../../src/Db/Query.php#L1576)

`public function getBindings(): array`

Get bind parameters

**➡️ Return value**

- Type: array

### paginate() · [source](../../src/Db/Query.php#L1589)

`public function paginate(int $page = 1, int $pageSize = 30, bool $reverse = false): Merlin\Db\Paginator`

Create a paginator for the current query

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$page` | int | `1` | Page number (1-based) |
| `$pageSize` | int | `30` | Number of items per page |
| `$reverse` | bool | `false` | Whether to reverse the order of results (for efficient deep pagination) |

**➡️ Return value**

- Type: [Paginator](Paginator.md)

### getRowCount() · [source](../../src/Db/Query.php#L1637)

`public function getRowCount(): int`

Return the number of affected rows for write operations or the number of rows in the result set for read operations

**➡️ Return value**

- Type: int

