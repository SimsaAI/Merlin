# 🧩 Class: ResultSet

**Full name:** [Merlin\Db\ResultSet](../../src/Db/ResultSet.php)

## 🚀 Public methods

### __construct() · [source](../../src/Db/ResultSet.php#L36)

`public function __construct(Merlin\Db\Database $connection, PDOStatement $statement, string|null $sqlStatement = null, array|null $boundParams = null, Merlin\Mvc\Model|null $model = null): mixed`

Create a new ResultSet wrapping a PDO statement result.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$connection` | [Database](Db_Database.md) | - | Database connection used to execute the query. |
| `$statement` | PDOStatement | - | The executed PDO statement. |
| `$sqlStatement` | string\|null | `null` | The original SQL string (used by reexecute()). |
| `$boundParams` | array\|null | `null` | Bound parameters (used by reexecute()). |
| `$model` | [Model](Mvc_Model.md)\|null | `null` | Optional model instance used for hydration (sets the fetch class). |

**➡️ Return value**

- Type: mixed


---

### fetch() · [source](../../src/Db/ResultSet.php#L56)

`public function fetch(): object|array|false`

Fetch next row as object or array depending on fetch mode.

**➡️ Return value**

- Type: object|array|false


---

### fetchArray() · [source](../../src/Db/ResultSet.php#L66)

`public function fetchArray(): array|false`

Fetch next row as associative array.

**➡️ Return value**

- Type: array|false


---

### fetchObject() · [source](../../src/Db/ResultSet.php#L76)

`public function fetchObject(): object|false`

Fetch next row as object.

**➡️ Return value**

- Type: object|false


---

### fetchColumn() · [source](../../src/Db/ResultSet.php#L86)

`public function fetchColumn(int $column = 0): mixed`

Fetch next row as a single column value.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$column` | int | `0` |  |

**➡️ Return value**

- Type: mixed


---

### fetchAllColumns() · [source](../../src/Db/ResultSet.php#L97)

`public function fetchAllColumns(int $column = 0): array`

Fetch all values from a single column.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$column` | int | `0` |  |

**➡️ Return value**

- Type: array


---

### fetchAll() · [source](../../src/Db/ResultSet.php#L109)

`public function fetchAll(int $fetchMode = 0, int $columnIndex = 0): array`

Fetch all rows as objects or arrays depending on fetch mode.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fetchMode` | int | `0` | Override fetch mode for this call (optional) |
| `$columnIndex` | int | `0` | Column index for PDO::FETCH_COLUMN mode (optional) |

**➡️ Return value**

- Type: array


---

### setFetchMode() · [source](../../src/Db/ResultSet.php#L120)

`public function setFetchMode(int $fetchMode): void`

Set the default fetch mode for this result set.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fetchMode` | int | - | One of the PDO::FETCH_* constants |

**➡️ Return value**

- Type: void


---

### allArrays() · [source](../../src/Db/ResultSet.php#L129)

`public function allArrays(): array`

Return all rows as associative arrays.

**➡️ Return value**

- Type: array


---

### allObjects() · [source](../../src/Db/ResultSet.php#L140)

`public function allObjects(): array`

Return all rows as objects.

**➡️ Return value**

- Type: array


---

### nextModel() · [source](../../src/Db/ResultSet.php#L151)

`public function nextModel(): Merlin\Mvc\Model|null`

Get the next model from the result set, or false if there are no more models. This method will attempt to hydrate a model if a model class was provided when the ResultSet was created. If no model class was provided, it will return false.

**➡️ Return value**

- Type: [Model](Mvc_Model.md)|null


---

### firstModel() · [source](../../src/Db/ResultSet.php#L188)

`public function firstModel(): Merlin\Mvc\Model|null`

Get first model or object from result set.

**➡️ Return value**

- Type: [Model](Mvc_Model.md)|null


---

### allModels() · [source](../../src/Db/ResultSet.php#L218)

`public function allModels(): array`

Get all remaining rows hydrated as model instances.

Calls {@see \nextModel()} repeatedly until the result set is exhausted.
Returns an empty array when no model class was provided at construction.

**➡️ Return value**

- Type: array


---

### getSql() · [source](../../src/Db/ResultSet.php#L236)

`public function getSql(): string|null`

Return the SQL statement that was executed to produce this result set, if available.

**➡️ Return value**

- Type: string|null


---

### getBindings() · [source](../../src/Db/ResultSet.php#L245)

`public function getBindings(): array|null`

Return the variables that were bound to the SQL statement, if available.

**➡️ Return value**

- Type: array|null


---

### reexecute() · [source](../../src/Db/ResultSet.php#L254)

`public function reexecute(): void`

Execute the query again to repopulate the result set.

**➡️ Return value**

- Type: void


---

### rewind() · [source](../../src/Db/ResultSet.php#L270)

`public function rewind(): void`

Rewind is a no-op: the result set cursor is forward-only.

**➡️ Return value**

- Type: void


---

### current() · [source](../../src/Db/ResultSet.php#L276)

`public function current(): mixed`

Return the current row (fetched lazily on first access).

**➡️ Return value**

- Type: mixed


---

### key() · [source](../../src/Db/ResultSet.php#L286)

`public function key(): int`

Return the zero-based position of the current row within this traversal.

**➡️ Return value**

- Type: int


---

### next() · [source](../../src/Db/ResultSet.php#L292)

`public function next(): void`

Advance to the next row.

**➡️ Return value**

- Type: void


---

### valid() · [source](../../src/Db/ResultSet.php#L299)

`public function valid(): bool`

Return true while the current row is not false/null (i.e., while rows remain).

**➡️ Return value**

- Type: bool


---

### count() · [source](../../src/Db/ResultSet.php#L308)

`public function count(): int`

Return the number of rows affected/returned by the underlying statement.

**➡️ Return value**

- Type: int
- Description: Row count as reported by PDOStatement::rowCount().



---

[Back to the Index ⤴](index.md)
