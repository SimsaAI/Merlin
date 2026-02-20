# 🧩 ResultSet

**Full name:** [Merlin\Db\ResultSet](../../src/Db/ResultSet.php)

## 🔐 Properties

- `protected` [🧩`Database`](Database.md) `$db` · [source](../../src/Db/ResultSet.php)
- `protected` `PDOStatement` `$statement` · [source](../../src/Db/ResultSet.php)
- `protected` 🔤 `string`|`null` `$sqlStatement` · [source](../../src/Db/ResultSet.php)
- `protected` 📦 `array`|`null` `$boundParams` · [source](../../src/Db/ResultSet.php)
- `protected` 🔤 `string`|`null` `$modelClass` · [source](../../src/Db/ResultSet.php)
- `protected` 🔢 `int` `$fetchMode` · [source](../../src/Db/ResultSet.php)
- `protected` 🎲 `mixed` `$firstObject` · [source](../../src/Db/ResultSet.php)
- `protected` 🎲 `mixed` `$currentRow` · [source](../../src/Db/ResultSet.php)
- `protected` 🔢 `int` `$position` · [source](../../src/Db/ResultSet.php)
- `protected` ⚙️ `bool` `$initialized` · [source](../../src/Db/ResultSet.php)

## 🚀 Public methods

### __construct() · [source](../../src/Db/ResultSet.php#L27)

`public function __construct(Merlin\Db\Database $connection, PDOStatement $statement, string|null $sqlStatement = null, array|null $boundParams = null, Merlin\Mvc\Model|null $model = null): mixed`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$connection` | [🧩`Database`](Database.md) | - |  |
| `$statement` | `PDOStatement` | - |  |
| `$sqlStatement` | 🔤 `string`\|`null` | `null` |  |
| `$boundParams` | 📦 `array`\|`null` | `null` |  |
| `$model` | [🧩`Model`](Model.md)\|`null` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### fetch() · [source](../../src/Db/ResultSet.php#L47)

`public function fetch(): object|array|false`

Fetch next row as object or array depending on fetch mode.

**➡️ Return value**

- Type: 🧱 `object`|📦 `array`|`false`

### fetchArray() · [source](../../src/Db/ResultSet.php#L57)

`public function fetchArray(): array|false`

Fetch next row as associative array.

**➡️ Return value**

- Type: 📦 `array`|`false`

### fetchObject() · [source](../../src/Db/ResultSet.php#L67)

`public function fetchObject(): object|false`

Fetch next row as object.

**➡️ Return value**

- Type: 🧱 `object`|`false`

### fetchColumn() · [source](../../src/Db/ResultSet.php#L77)

`public function fetchColumn(int $column = 0): mixed`

Fetch next row as a single column value.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$column` | 🔢 `int` | `0` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### fetchAllColumns() · [source](../../src/Db/ResultSet.php#L88)

`public function fetchAllColumns(int $column = 0): array`

Fetch all values from a single column.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$column` | 🔢 `int` | `0` |  |

**➡️ Return value**

- Type: 📦 `array`

### fetchAll() · [source](../../src/Db/ResultSet.php#L100)

`public function fetchAll(int $fetchMode = 0, int $columnIndex = 0): array`

Fetch all rows as objects or arrays depending on fetch mode.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$fetchMode` | 🔢 `int` | `0` | Override fetch mode for this call (optional) |
| `$columnIndex` | 🔢 `int` | `0` | Column index for PDO::FETCH_COLUMN mode (optional) |

**➡️ Return value**

- Type: 📦 `array`

### setFetchMode() · [source](../../src/Db/ResultSet.php#L111)

`public function setFetchMode(int $fetchMode): void`

Set the default fetch mode for this result set.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$fetchMode` | 🔢 `int` | - | One of the PDO::FETCH_* constants |

**➡️ Return value**

- Type: `void`

### allArrays() · [source](../../src/Db/ResultSet.php#L120)

`public function allArrays(): array`

Return all rows as associative arrays.

**➡️ Return value**

- Type: 📦 `array`

### allObjects() · [source](../../src/Db/ResultSet.php#L131)

`public function allObjects(): array`

Return all rows as objects.

**➡️ Return value**

- Type: 📦 `array`

### nextModel() · [source](../../src/Db/ResultSet.php#L142)

`public function nextModel(): Merlin\Mvc\Model|null`

Get the next model from the result set, or false if there are no more models. This method will attempt to hydrate a model if a model class was provided when the ResultSet was created. If no model class was provided, it will return false.

**➡️ Return value**

- Type: [🧩`Model`](Model.md)|`null`

### firstModel() · [source](../../src/Db/ResultSet.php#L179)

`public function firstModel(): Merlin\Mvc\Model|null`

Get first model or object from result set.

**➡️ Return value**

- Type: [🧩`Model`](Model.md)|`null`

### allModels() · [source](../../src/Db/ResultSet.php#L201)

`public function allModels(): array`

Get all remaining models or objects from result set.

**➡️ Return value**

- Type: 📦 `array`

### getSql() · [source](../../src/Db/ResultSet.php#L219)

`public function getSql(): string|null`

Return the SQL statement that was executed to produce this result set, if available.

**➡️ Return value**

- Type: 🔤 `string`|`null`

### getBindings() · [source](../../src/Db/ResultSet.php#L228)

`public function getBindings(): array|null`

Return the variables that were bound to the SQL statement, if available.

**➡️ Return value**

- Type: 📦 `array`|`null`

### reexecute() · [source](../../src/Db/ResultSet.php#L237)

`public function reexecute(): void`

Execute the query again to repopulate the result set.

**➡️ Return value**

- Type: `void`

### rewind() · [source](../../src/Db/ResultSet.php#L252)

`public function rewind(): void`

**➡️ Return value**

- Type: `void`

### current() · [source](../../src/Db/ResultSet.php#L257)

`public function current(): mixed`

**➡️ Return value**

- Type: 🎲 `mixed`

### key() · [source](../../src/Db/ResultSet.php#L266)

`public function key(): int`

**➡️ Return value**

- Type: 🔢 `int`

### next() · [source](../../src/Db/ResultSet.php#L271)

`public function next(): void`

**➡️ Return value**

- Type: `void`

### valid() · [source](../../src/Db/ResultSet.php#L277)

`public function valid(): bool`

**➡️ Return value**

- Type: ⚙️ `bool`

### count() · [source](../../src/Db/ResultSet.php#L282)

`public function count(): int`

**➡️ Return value**

- Type: 🔢 `int`

