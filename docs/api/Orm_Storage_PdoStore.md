# 🧩 Class: PdoStore

**Full name:** [Azera\Orm\Storage\PdoStore](../../src/Orm/Storage/PdoStore.php)

SQL backend of the [`Store`](Orm_Storage_Store.md) seam over a [`Database`](Db_Database.md).

BORROWS, never owns: holds read/write ROLE STRINGS and resolves the live
connection via DatabaseManager per operation â€” the same pattern as
Model::readConnection(). Consequences: QB + legacy save() + ORM flush all
share ONE connection per role (transactions join the caller's nesting);
per-call role resolution keeps dynamic tenancy; cost = one cached array
read. ALL SQL goes through the connection so Db events fire (tracking).

Holds the RETURNING matrix: pk_set -> plain INSERT; all non-PK cols set +
driver RETURNING -> RETURNING id; unset non-PK cols -> RETURNING *;
no-RETURNING driver -> lastInsertId.

Connection-role resolution is PER CLASS: metadata readRole/writeRole
(compiled from #[Connection(read|write|role)]) override the constructor
defaults, so one shared store instance can route individual classes to
dedicated connections. Transactions are PER CONNECTION TARGET: begin($meta)
opens a tx on $meta's resolved write connection and records it in the tx
map — operations resolving to the SAME Database join it (reads inside the
tx see its uncommitted writes), operations resolving to OTHER connections
route to their own class's roles. One store instance therefore holds one
tx PER DISTINCT write target (flushAll() commits them independently);
legacy Model/QB writes on an already-begun target join via the shared
Database instance.

## 🚀 Public methods

### __construct() · [source](../../src/Orm/Storage/PdoStore.php#L48)

`public function __construct(Azera\Db\DatabaseManager|null $dbm = null, string $readRole = 'read', string $writeRole = 'write'): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$dbm` | [DatabaseManager](Db_DatabaseManager.md)\|null | `null` |  |
| `$readRole` | string | `'read'` |  |
| `$writeRole` | string | `'write'` |  |

**➡️ Return value**

- Type: mixed


---

### wantsNativeValues() · [source](../../src/Orm/Storage/PdoStore.php#L63)

`public function wantsNativeValues(): bool`

SQL shaping applies: DateTime objects are formatted and cast values
are ENCODED before the row hits the connection (the EM's
extractData() consults this via the Store seam).

**➡️ Return value**

- Type: bool


---

### txTarget() · [source](../../src/Orm/Storage/PdoStore.php#L73)

`public function txTarget(array $meta): string`

Connection identity for tx grouping in flush(): the class's write
role (#[Connection] override wins over the constructor default) —
two classes sharing a write role share one transaction target.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array | - |  |

**➡️ Return value**

- Type: string


---

### insertOne() · [source](../../src/Orm/Storage/PdoStore.php#L103)

`public function insertOne(string $class, array $data): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - |  |

**➡️ Return value**

- Type: array


---

### updateOne() · [source](../../src/Orm/Storage/PdoStore.php#L149)

`public function updateOne(string $class, array $data, array $id): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - |  |
| `$id` | array | - |  |

**➡️ Return value**

- Type: array


---

### upsertOne() · [source](../../src/Orm/Storage/PdoStore.php#L160)

`public function upsertOne(string $class, array $data): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - |  |

**➡️ Return value**

- Type: array


---

### deleteOne() · [source](../../src/Orm/Storage/PdoStore.php#L194)

`public function deleteOne(string $class, array $id): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - |  |

**➡️ Return value**

- Type: void


---

### findBy() · [source](../../src/Orm/Storage/PdoStore.php#L202)

`public function findBy(string $class, array $where): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | - |  |

**➡️ Return value**

- Type: array


---

### findByPk() · [source](../../src/Orm/Storage/PdoStore.php#L211)

`public function findByPk(string $class, array $id): array|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - |  |

**➡️ Return value**

- Type: array|null


---

### count() · [source](../../src/Orm/Storage/PdoStore.php#L217)

`public function count(string $class, array $where = []): int`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | `[]` |  |

**➡️ Return value**

- Type: int


---

### begin() · [source](../../src/Orm/Storage/PdoStore.php#L235)

`public function begin(array|null $meta = null): void`

begin($meta) opens a transaction on $meta's write connection (the
metadata writeRole override wins over the constructor default;
null meta = constructor default). Idempotent per connection: an
ALREADY-HELD tx on the same Database joins (no savepoint — two
write roles aliasing one connection share ONE tx, one BEGIN in the
log). Caller-opened txs are never recorded here: they are joined
implicitly by routing and never committed/rolled back by this store.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### commit() · [source](../../src/Orm/Storage/PdoStore.php#L252)

`public function commit(array|null $meta = null): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### rollback() · [source](../../src/Orm/Storage/PdoStore.php#L260)

`public function rollback(array|null $meta = null): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### inTransaction() · [source](../../src/Orm/Storage/PdoStore.php#L274)

`public function inTransaction(array|null $meta = null): bool`

$meta form: whether a tx is active on $meta's write connection —
store-begun OR caller-opened (flush()/flushAll() join either, and
must not double-begin over a caller tx). Bare form: any tx this
store began, else the constructor-default write connection.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: bool


---

### enrichMetadata() · [source](../../src/Orm/Storage/PdoStore.php#L521)

`public function enrichMetadata(array $meta, ReflectionClass $class): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array | - |  |
| `$class` | ReflectionClass | - |  |

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](README.md)
