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
dedicated connections. Once begin() opens a transaction, ALL statements
pin to that transaction connection until commit/rollback — a tx must not
split across connections, and reads must see its uncommitted writes
(per-class routing applies to autocommit statements only).

## 🚀 Public methods

### __construct() · [source](../../src/Orm/Storage/PdoStore.php#L37)

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

### wantsNativeValues() · [source](../../src/Orm/Storage/PdoStore.php#L52)

`public function wantsNativeValues(): bool`

SQL shaping applies: DateTime objects are formatted and cast values
are ENCODED before the row hits the connection (the EM's
extractData() consults this via the Store seam).

**➡️ Return value**

- Type: bool


---

### txTarget() · [source](../../src/Orm/Storage/PdoStore.php#L62)

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

### insertOne() · [source](../../src/Orm/Storage/PdoStore.php#L89)

`public function insertOne(string $class, array $data): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - |  |

**➡️ Return value**

- Type: array


---

### updateOne() · [source](../../src/Orm/Storage/PdoStore.php#L135)

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

### upsertOne() · [source](../../src/Orm/Storage/PdoStore.php#L146)

`public function upsertOne(string $class, array $data): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - |  |

**➡️ Return value**

- Type: array


---

### deleteOne() · [source](../../src/Orm/Storage/PdoStore.php#L180)

`public function deleteOne(string $class, array $id): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - |  |

**➡️ Return value**

- Type: void


---

### findBy() · [source](../../src/Orm/Storage/PdoStore.php#L188)

`public function findBy(string $class, array $where): array`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | - |  |

**➡️ Return value**

- Type: array


---

### findByPk() · [source](../../src/Orm/Storage/PdoStore.php#L197)

`public function findByPk(string $class, array $id): array|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - |  |

**➡️ Return value**

- Type: array|null


---

### count() · [source](../../src/Orm/Storage/PdoStore.php#L203)

`public function count(string $class, array $where = []): int`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | `[]` |  |

**➡️ Return value**

- Type: int


---

### begin() · [source](../../src/Orm/Storage/PdoStore.php#L221)

`public function begin(array|null $meta = null): void`

begin($meta) pins the scheduled class's WRITE target (metadata
writeRole override wins over the constructor default — flush() passes
the first scheduled class's meta so per-class routing survives tx
pinning): every subsequent operation routes to it until
commit/rollback, so a transaction can never split across connections
and reads inside it see uncommitted writes. begin() without meta
(direct callers, tests) pins the constructor default.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### commit() · [source](../../src/Orm/Storage/PdoStore.php#L227)

`public function commit(): void`

**➡️ Return value**

- Type: void


---

### rollback() · [source](../../src/Orm/Storage/PdoStore.php#L233)

`public function rollback(): void`

**➡️ Return value**

- Type: void


---

### inTransaction() · [source](../../src/Orm/Storage/PdoStore.php#L239)

`public function inTransaction(): bool`

**➡️ Return value**

- Type: bool


---

### enrichMetadata() · [source](../../src/Orm/Storage/PdoStore.php#L460)

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
