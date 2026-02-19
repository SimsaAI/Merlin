# 🧩 Merlin\Db\ResultSet

## 🔐 Properties

- `protected Merlin\Db\Database $db`
- `protected PDOStatement $statement`
- `protected string|null $sqlStatement`
- `protected array|null $boundParams`
- `protected string|null $modelClass`
- `protected 🔢 int $fetchMode`
- `protected 🎲 mixed $firstObject`
- `protected 🎲 mixed $currentRow`
- `protected 🔢 int $position`
- `protected ⚙️ bool $initialized`

## 🚀 Public methods

### `__construct()`

`public function __construct(Merlin\Db\Database $connection, PDOStatement $statement, string|null $sqlStatement = null, array|null $boundParams = null, Merlin\Mvc\Model|null $model = null) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$connection` | `Merlin\Db\Database` | `` |  |
| `$statement` | `PDOStatement` | `` |  |
| `$sqlStatement` | `string\|null` | `null` |  |
| `$boundParams` | `array\|null` | `null` |  |
| `$model` | `Merlin\Mvc\Model\|null` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `fetch()`

`public function fetch() : object|array|false`

Fetch next row as object or array depending on fetch mode.

**➡️ Return value**

- Type: `object|array|false`

### `fetchArray()`

`public function fetchArray() : array|false`

Fetch next row as associative array.

**➡️ Return value**

- Type: `array|false`

### `fetchObject()`

`public function fetchObject() : object|false`

Fetch next row as object.

**➡️ Return value**

- Type: `object|false`

### `fetchColumn()`

`public function fetchColumn(int $column = 0) : mixed`

Fetch next row as a single column value.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$column` | `🔢 int` | `0` |  |

**➡️ Return value**

- Type: `mixed`

### `fetchAllColumns()`

`public function fetchAllColumns(int $column = 0) : array`

Fetch all values from a single column.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$column` | `🔢 int` | `0` |  |

**➡️ Return value**

- Type: `array`

### `fetchAll()`

`public function fetchAll(int $fetchMode = 0, int $columnIndex = 0) : array`

Fetch all rows as objects or arrays depending on fetch mode.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fetchMode` | `🔢 int` | `0` | Override fetch mode for this call (optional) |
| `$columnIndex` | `🔢 int` | `0` | Column index for PDO::FETCH_COLUMN mode (optional) |

**➡️ Return value**

- Type: `array`

### `setFetchMode()`

`public function setFetchMode(int $fetchMode) : void`

Set the default fetch mode for this result set.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fetchMode` | `🔢 int` | `` | One of the PDO::FETCH_* constants |

**➡️ Return value**

- Type: `void`

### `allArrays()`

`public function allArrays() : array`

Return all rows as associative arrays.

**➡️ Return value**

- Type: `array`

### `allObjects()`

`public function allObjects() : array`

Return all rows as objects.

**➡️ Return value**

- Type: `array`

### `nextModel()`

`public function nextModel() : Merlin\Mvc\Model|null`

Get the next model from the result set, or false if there are no more models. This method will attempt to hydrate a model if a model class was provided when the ResultSet was created. If no model class was provided, it will return false.

**➡️ Return value**

- Type: `Merlin\Mvc\Model|null`

### `firstModel()`

`public function firstModel() : Merlin\Mvc\Model|null`

Get first model or object from result set.

**➡️ Return value**

- Type: `Merlin\Mvc\Model|null`

### `allModels()`

`public function allModels() : array`

Get all remaining models or objects from result set.

**➡️ Return value**

- Type: `array`

### `getSql()`

`public function getSql() : string|null`

Return the SQL statement that was executed to produce this result set, if available.

**➡️ Return value**

- Type: `string|null`

### `getBindings()`

`public function getBindings() : array|null`

Return the variables that were bound to the SQL statement, if available.

**➡️ Return value**

- Type: `array|null`

### `reexecute()`

`public function reexecute() : void`

Execute the query again to repopulate the result set.

**➡️ Return value**

- Type: `void`

### `rewind()`

`public function rewind() : void`

**➡️ Return value**

- Type: `void`

### `current()`

`public function current() : mixed`

**➡️ Return value**

- Type: `mixed`

### `key()`

`public function key() : int`

**➡️ Return value**

- Type: `int`

### `next()`

`public function next() : void`

**➡️ Return value**

- Type: `void`

### `valid()`

`public function valid() : bool`

**➡️ Return value**

- Type: `bool`

### `count()`

`public function count() : int`

**➡️ Return value**

- Type: `int`

