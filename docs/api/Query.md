# 🧩 Merlin\Db\Query

Unified query builder for SELECT, INSERT, UPDATE, DELETE operations

## 📌 Constants

- **PI_DEFAULT** = `0`
- **PI_COLUMN** = `1`
- **PI_TABLE** = `2`

## 🔐 Properties

- `protected static ⚙️ bool $useModels`
- `protected static 📦 array $modelCache`
- `protected static Merlin\Mvc\ModelMapping|null $modelMapping`
- `protected Merlin\Mvc\Model|null $model`
- `protected 📦 array $bindParams`
- `protected 🔢 int $limit`
- `protected 🔢 int $offset`
- `protected 🔢 int $rowCount`
- `protected ⚙️ bool $isReadQuery`
- `protected ⚙️ bool $hasResultSet`
- `protected array|null $columns`
- `protected 📦 array $joins`
- `protected 📦 array $orderBy`
- `protected 📦 array $values`
- `protected ⚙️ bool $getModelDb`
- `protected string|null $table`
- `protected ⚙️ bool $returnSql`
- `protected 📦 array $groupBy`
- `protected ⚙️ bool $forUpdate`
- `protected ⚙️ bool $sharedLock`
- `protected ⚙️ bool $distinct`
- `protected 🔤 string $preColumnInjection`
- `protected ⚙️ bool $replaceInto`
- `protected ⚙️ bool $ignore`
- `protected 📦 array $updateValues`
- `protected ⚙️ bool $updateValuesIsList`
- `protected array|string $conflictTarget`
- `protected array|string|null $returning`
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

### `useModels()`

`public static function useModels(bool $useModels) : void`

Enable or disable automatic model resolution for queries. If enabled, the query will resolve table names and database connections from model classes. If disabled, the query will treat table names as literal and use database connections from AppContext. This can be useful for simple queries or when you want to avoid coupling to model classes.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$useModels` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `void`

### `setModelMapping()`

`public static function setModelMapping(Merlin\Mvc\ModelMapping|null $modelMapping) : void`

Set the model mapping instance to use for resolving model class names to table names and database connections. This can be used instead of model classes for simple queries or when you want to avoid coupling to model classes.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$modelMapping` | `Merlin\Mvc\ModelMapping\|null` | `` |  |

**➡️ Return value**

- Type: `void`

### `__construct()`

`public function __construct(Merlin\Db\Database|null $db = null, Merlin\Mvc\Model|null $model = null) : mixed`

Constructor. Can optionally pass a Database connection to use for this query, or a Model to automatically set the table and connection.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | `Merlin\Db\Database\|null` | `null` |  |
| `$model` | `Merlin\Mvc\Model\|null` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `new()`

`public static function new(Merlin\Db\Database|null $db = null) : static`

Factory method to create a new Query instance. Can optionally pass a Database connection to use for this query.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$db` | `Merlin\Db\Database\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

### `table()`

`public function table(string $name, string|null $alias = null) : static`

Set the table for this query. Can be either a table name or a model class name. If a model class name is provided, the corresponding table will be used and the model's database connection will be used if no connection is set on the query.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | Table name or model class name |
| `$alias` | `string\|null` | `null` | Optional table alias |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `columns()`

`public function columns(array|string $columns) : static`

Set columns for SELECT queries. Can be either a comma-separated string or an array of column names.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$columns` | `array\|string` | `` |  |

**➡️ Return value**

- Type: `static`

### `limit()`

`public function limit(int $limit, int $offset = 0) : static`

Set the LIMIT and optional OFFSET for SELECT queries
(or limit number of rows affected for UPDATE/DELETE)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$limit` | `🔢 int` | `` |  |
| `$offset` | `🔢 int` | `0` |  |

**➡️ Return value**

- Type: `static`

### `offset()`

`public function offset(int $offset) : static`

Sets an OFFSET clause for SELECT queries

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$offset` | `🔢 int` | `` |  |

**➡️ Return value**

- Type: `static`

### `values()`

`public function values(object|array $values, bool $escape = true) : static`

Adds values for INSERT or UPDATE queries. Can be either:
- An associative array of column => value pairs
- An object with public properties

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$values` | `object\|array` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `bulkValues()`

`public function bulkValues(array $valuesList = [], bool $escape = true) : static`

Set multiple rows of values for bulk insert operations.

Each item in the list should be an array of column => value pairs.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$valuesList` | `📦 array` | `[]` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `hasValues()`

`public function hasValues() : bool`

Check if any values have been set for this query

**➡️ Return value**

- Type: `bool`

### `set()`

`public function set(array|string $column, mixed $value = null, bool $escape = true) : static`

Set a value for INSERT or UPDATE queries. Can be either:
- A single column name and value pair
- An associative array of column => value pairs

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$column` | `array\|string` | `` |  |
| `$value` | `🎲 mixed` | `null` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `join()`

`public function join(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null, string|null $type = null) : static`

Add a JOIN clause to the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$model` | `🔤 string` | `` |  |
| `$alias` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$conditions` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$type` | `string\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `innerJoin()`

`public function innerJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null) : static`

Adds an INNER join to the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$model` | `🔤 string` | `` |  |
| `$alias` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$conditions` | `Merlin\Db\Condition\|string\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `leftJoin()`

`public function leftJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null) : static`

Adds a LEFT join to the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$model` | `🔤 string` | `` |  |
| `$alias` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$conditions` | `Merlin\Db\Condition\|string\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `rightJoin()`

`public function rightJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null) : static`

Adds a RIGHT join to the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$model` | `🔤 string` | `` |  |
| `$alias` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$conditions` | `Merlin\Db\Condition\|string\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `crossJoin()`

`public function crossJoin(string $model, Merlin\Db\Condition|string|null $alias = null, Merlin\Db\Condition|string|null $conditions = null) : static`

Adds a CROSS join to the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$model` | `🔤 string` | `` |  |
| `$alias` | `Merlin\Db\Condition\|string\|null` | `null` |  |
| `$conditions` | `Merlin\Db\Condition\|string\|null` | `null` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `orderBy()`

`public function orderBy(array|string $orderBy) : static`

Set ORDER BY clause

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$orderBy` | `array\|string` | `` |  |

**➡️ Return value**

- Type: `static`

### `bind()`

`public function bind(object|array $bindParams) : static`

Bind parameters for prepared statements. Can be either an associative array or an object with properties as parameter names.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$bindParams` | `object\|array` | `` |  |

**➡️ Return value**

- Type: `static`

### `returnSql()`

`public function returnSql(bool $returnSql = true) : static`

Set whether to return the SQL string instead of executing the query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$returnSql` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `distinct()`

`public function distinct(bool $distinct) : static`

Set DISTINCT modifier for SELECT queries

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$distinct` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `static`

### `injectBeforeColumns()`

`public function injectBeforeColumns(string $inject) : static`

Set a string to be injected before the column list in SELECT queries (e.g. for SQL_CALC_FOUND_ROWS in MySQL)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$inject` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `groupBy()`

`public function groupBy(array|string $groupBy) : static`

Set GROUP BY clause

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$groupBy` | `array\|string` | `` |  |

**➡️ Return value**

- Type: `static`

### `forUpdate()`

`public function forUpdate(bool $forUpdate) : static`

Sets a FOR UPDATE clause (MySQL/PostgreSQL) or FOR SHARE (PostgreSQL)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$forUpdate` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `static`

### `sharedLock()`

`public function sharedLock(bool $sharedLock) : static`

Sets a LOCK IN SHARE MODE / FOR SHARE clause (MySQL/PostgreSQL)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$sharedLock` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `static`

### `replace()`

`public function replace(bool $replace = true) : static`

Mark this as a REPLACE INTO operation (MySQL/SQLite)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$replace` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `ignore()`

`public function ignore(bool $ignore = true) : static`

Set IGNORE modifier for INSERT (MySQL/SQLite) or ON CONFLICT DO NOTHING (PostgreSQL)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$ignore` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `updateValues()`

`public function updateValues(array $updateValues, bool $escape = true) : static`

Set values for ON CONFLICT/ON DUPLICATE KEY UPDATE clause. Can be either:
- List array -> EXCLUDED/VALUES mode
- Assoc array -> explicit values

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$updateValues` | `📦 array` | `` |  |
| `$escape` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `conflict()`

`public function conflict(array|string $columnsOrConstraint) : static`

Set conflict target for ON CONFLICT clause (PostgreSQL). Can be either:
- Array with column names
- String with column names or constraint name

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$columnsOrConstraint` | `array\|string` | `` |  |

**➡️ Return value**

- Type: `static`

### `returning()`

`public function returning(array|string|null $columns) : static`

Set columns to return from an INSERT/UPDATE/DELETE query. Supported by PostgreSQL (RETURNING) and MySQL (RETURNING with MySQL 8.0.27+)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$columns` | `array\|string\|null` | `` |  |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception 

### `toSql()`

`public function toSql() : string`

Compile and return the SQL string for this query without executing it

**➡️ Return value**

- Type: `string`

**⚠️ Throws**

- \Exception 

### `select()`

`public function select(array|string|null $columns = null) : Merlin\Db\ResultSet|string`

Execute SELECT query and return ResultSet or return SQL string if returnSql is enabled

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$columns` | `array\|string\|null` | `null` | Columns to select, or null to ignore parameter. Can be either a comma-separated string or an array of column names. |

**➡️ Return value**

- Type: `Merlin\Db\ResultSet|string`

**⚠️ Throws**

- \Exception 

### `first()`

`public function first() : Merlin\Mvc\Model|string|null`

Execute SELECT query and return first model or null or return SQL string if returnSql is enabled

**➡️ Return value**

- Type: `Merlin\Mvc\Model|string|null`
- Description: First model, or SQL string, or null if no results

**⚠️ Throws**

- \Exception 

### `insert()`

`public function insert(array|null $data = null) : Merlin\Db\ResultSet|array|string|bool`

Execute INSERT or UPSERT query or return SQL string if returnSql is enabled

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | `array\|null` | `null` | Data to insert |

**➡️ Return value**

- Type: `Merlin\Db\ResultSet|array|string|bool`
- Description: Insert ID, true on success, or SQL string, or result of returning clause

**⚠️ Throws**

- \Exception 

### `upsert()`

`public function upsert(array|null $data = null) : Merlin\Db\ResultSet|array|string|bool`

Execute UPSERT query (INSERT with ON CONFLICT/ON DUPLICATE KEY UPDATE) or return SQL string if returnSql is enabled

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | `array\|null` | `null` | Data to insert |

**➡️ Return value**

- Type: `Merlin\Db\ResultSet|array|string|bool`
- Description: Insert ID, true on success, or SQL string, or result of returning clause

**⚠️ Throws**

- \Exception 

### `update()`

`public function update(array|null $data = null) : Merlin\Db\ResultSet|array|string|int`

Execute UPDATE query or return SQL string if returnSql is enabled

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | `array\|null` | `null` | Data to update |

**➡️ Return value**

- Type: `Merlin\Db\ResultSet|array|string|int`
- Description: Number of affected rows or SQL string, or row of returning clause

**⚠️ Throws**

- \Exception 

### `delete()`

`public function delete() : Merlin\Db\ResultSet|array|string|int`

Execute DELETE query

**➡️ Return value**

- Type: `Merlin\Db\ResultSet|array|string|int`
- Description: Number of affected rows, SQL string, or result of returning clause

**⚠️ Throws**

- \Exception 

### `truncate()`

`public function truncate() : string|int`

Execute TRUNCATE query or return SQL string if returnSql is enabled

**➡️ Return value**

- Type: `string|int`
- Description: Number of affected rows or SQL string

**⚠️ Throws**

- \Exception 

### `exists()`

`public function exists() : string|bool`

Check if any rows exist matching the query

**➡️ Return value**

- Type: `string|bool`

**⚠️ Throws**

- \Exception 

### `count()`

`public function count() : string|int`

Count rows matching the query

**➡️ Return value**

- Type: `string|int`
- Description: Number of matching rows or SQL string

**⚠️ Throws**

- \Exception 

### `getBindings()`

`public function getBindings() : array`

Get bind parameters

**➡️ Return value**

- Type: `array`

### `paginate()`

`public function paginate(int $page = 1, int $pageSize = 30, bool $reverse = false) : Merlin\Db\Paginator`

Create a paginator for the current query

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$page` | `🔢 int` | `1` | Page number (1-based) |
| `$pageSize` | `🔢 int` | `30` | Number of items per page |
| `$reverse` | `⚙️ bool` | `false` | Whether to reverse the order of results (for efficient deep pagination) |

**➡️ Return value**

- Type: `Merlin\Db\Paginator`

### `getRowCount()`

`public function getRowCount() : int`

Return the number of affected rows for write operations or the number of rows in the result set for read operations

**➡️ Return value**

- Type: `int`

