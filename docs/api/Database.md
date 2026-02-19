# 🧩 Merlin\Db\Database

Class Database

## 🔐 Properties

- `protected 🔤 string $connectString`
- `protected 🔤 string $user`
- `protected 🔤 string $driverName`
- `protected 🔤 string $pass`
- `protected 📦 array $options`
- `protected PDO $pdo`
- `protected PDOStatement $statement`
- `protected 🔢 int $transactionLevel`
- `protected 🔤 string $quoteChar`
- `protected array|bool $autoReconnect`
- `protected 📦 array $listeners`

## 🚀 Public methods

### `__construct()`

`public function __construct(string $dsn, string $user = '', string $pass = '', array $options = []) : mixed`

Create a new database connection using the provided DSN, credentials and options.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dsn` | `🔤 string` | `` |  |
| `$user` | `🔤 string` | `''` |  |
| `$pass` | `🔤 string` | `''` |  |
| `$options` | `📦 array` | `[]` |  |

**➡️ Return value**

- Type: `mixed`

**⚠️ Throws**

- \Exception 

### `connect()`

`public function connect() : mixed`

Establish a new PDO connection using the current configuration

**➡️ Return value**

- Type: `mixed`

**⚠️ Throws**

- \Exception 

### `addListener()`

`public function addListener(callable $listener) : void`

Add an event listener for database events

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$listener` | `callable` | `` | A callable that receives the event name and relevant data |

**➡️ Return value**

- Type: `void`

### `setAutoReconnect()`

`public function setAutoReconnect(bool $enabled = true, int $maxAttempts = 0, float $retryDelay = 1, float $backoffMultiplier = 2, float $maxRetryDelay = 30, bool $jitter = true, callable|null $onReconnect = null) : static`

Configure automatic reconnection behavior with detailed options

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$enabled` | `⚙️ bool` | `true` | Enable or disable auto-reconnect |
| `$maxAttempts` | `🔢 int` | `0` | Maximum number of retry attempts (0 for unlimited) |
| `$retryDelay` | `🌡️ float` | `1` | Initial delay between retries in seconds |
| `$backoffMultiplier` | `🌡️ float` | `2` | Multiplier for exponential backoff |
| `$maxRetryDelay` | `🌡️ float` | `30` | Maximum delay between retries in seconds |
| `$jitter` | `⚙️ bool` | `true` | Whether to add random jitter to retry delays |
| `$onReconnect` | `callable\|null` | `null` | Optional callback invoked on successful reconnect (receives attempt number and db instance) |

**➡️ Return value**

- Type: `static`

### `getAutoReconnect()`

`public function getAutoReconnect() : array|bool`

Get auto-reconnect configuration

**➡️ Return value**

- Type: `array|bool`

### `query()`

`public function query(string $query, array|null $params = null) : PDOStatement|bool`

Execute a SQL query with optional parameters and return the resulting statement or success status.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$query` | `🔤 string` | `` | SQL query to execute |
| `$params` | `array\|null` | `null` | Optional parameters for prepared statements |

**➡️ Return value**

- Type: `PDOStatement|bool`

**⚠️ Throws**

- \Exception 

### `prepare()`

`public function prepare(string $query) : PDOStatement|bool`

Prepare a SQL statement and return the resulting PDOStatement object.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$query` | `🔤 string` | `` | SQL query to prepare |

**➡️ Return value**

- Type: `PDOStatement|bool`

**⚠️ Throws**

- \Exception 

### `execute()`

`public function execute(array $params = []) : PDOStatement|bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$params` | `📦 array` | `[]` |  |

**➡️ Return value**

- Type: `PDOStatement|bool`

**⚠️ Throws**

- \Exception 

### `selectRow()`

`public function selectRow(string $query, array|null $params = null, int $fetchMode = 0) : array|bool`

Fetch a single row from the database as object, associative array, or numeric array depending on the specified fetch mode.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$query` | `🔤 string` | `` |  |
| `$params` | `array\|null` | `null` |  |
| `$fetchMode` | `🔢 int` | `0` |  |

**➡️ Return value**

- Type: `array|bool`

### `selectAll()`

`public function selectAll(string $query, array|null $params = null, int $fetchMode = 0) : array`

Fetch all rows from the database as an array of objects, associative arrays, or numeric arrays depending on the specified fetch mode.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$query` | `🔤 string` | `` |  |
| `$params` | `array\|null` | `null` |  |
| `$fetchMode` | `🔢 int` | `0` |  |

**➡️ Return value**

- Type: `array`

### `rowCount()`

`public function rowCount() : int`

**➡️ Return value**

- Type: `int`

### `lastInsertId()`

`public function lastInsertId(string|null $table = null, string|null $field = null) : string|bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$table` | `string\|null` | `null` |  |
| `$field` | `string\|null` | `null` |  |

**➡️ Return value**

- Type: `string|bool`

### `begin()`

`public function begin(bool $nesting = true) : int|bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$nesting` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `int|bool`

### `commit()`

`public function commit(bool $nesting = true) : int|bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$nesting` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `int|bool`

### `rollback()`

`public function rollback(bool $nesting = true) : int|bool`

Rollback the current transaction or to a savepoint if nesting is enabled and supported by the driver.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$nesting` | `⚙️ bool` | `true` | Whether to use savepoints for nested transactions (if supported by the driver) |

**➡️ Return value**

- Type: `int|bool`

**⚠️ Throws**

- \Exception 

### `quote()`

`public function quote(string|null $str) : string|bool`

Quote a string for use in a query.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$str` | `string\|null` | `` |  |

**➡️ Return value**

- Type: `string|bool`

### `quoteIdentifier()`

`public function quoteIdentifier(string|null ...$args) : string`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$args` | `string\|null` | `` | ...?string $args |

**➡️ Return value**

- Type: `string`

### `getInternalConnection()`

`public function getInternalConnection() : PDO|null`

**➡️ Return value**

- Type: `PDO|null`

### `builder()`

`public function builder() : Merlin\Db\Query`

Create a new Query builder instance associated with this database connection.

**➡️ Return value**

- Type: `Merlin\Db\Query`

### `getDriver()`

`public function getDriver() : string`

**➡️ Return value**

- Type: `string`

