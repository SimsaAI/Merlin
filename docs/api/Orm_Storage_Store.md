# 🔌 Interface: Store

**Full name:** [Azera\Orm\Storage\Store](../../src/Orm/Storage/Store.php)

Persistence-level seam between the ORM and any storage backend.

Operations the EntityManager's write pipeline performs — NOT a query builder. SQL stores
implement it over a [`Database`](Db_Database.md); Mongo over the
mongodb library. The per-situation write strategies (RETURNING matrix)
live in each backend. A model belongs to exactly one store, routed by
metadata `store` (#[Entity(store: 'name')]) — the registry key
EntityManager::setStore() maps to an instance. Third-party backends:
implement this interface, register under a name, annotate #[Entity].

## 🚀 Public methods

### wantsNativeValues() · [source](../../src/Orm/Storage/Store.php#L24)

`public function wantsNativeValues(): bool`

Capability flag: TRUE when this store wants values passed through
RAW (no DateTime formatting, no cast encoding) — the backend owns
value mapping (mongo: the driver owns BSON encoding). FALSE for
SQL-shaped stores (DateTime -> 'Y-m-d H:i:s', cast->encode()).

**➡️ Return value**

- Type: bool


---

### txTarget() · [source](../../src/Orm/Storage/Store.php#L32)

`public function txTarget(array $meta): string`

Connection identity for the class described by $meta: two classes
sharing one txTarget share one transaction target in flush().

Borrowing stores (SQL) derive it from the write role; owning stores
return a constant token (their connection is fixed per instance).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array | - |  |

**➡️ Return value**

- Type: string


---

### insertOne() · [source](../../src/Orm/Storage/Store.php#L40)

`public function insertOne(string $class, array $data): array`

Persist one entity: INSERT or UPDATE (upsert when flagged).

Returns raw row(s) for backfill: ['row' => ?array, 'id' => int|string|null].

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - | column-name-keyed raw values |

**➡️ Return value**

- Type: array


---

### updateOne() · [source](../../src/Orm/Storage/Store.php#L49)

`public function updateOne(string $class, array $data, array $id): array`

Update one entity by PK values.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - | column-name-keyed changed values |
| `$id` | array | - | PK field => value |

**➡️ Return value**

- Type: array


---

### upsertOne() · [source](../../src/Orm/Storage/Store.php#L62)

`public function upsertOne(string $class, array $data): array`

Atomic UPSERT: INSERT ... ON CONFLICT DO UPDATE (SQL) / updateOne
with upsert:true (Mongo). The caller-set PK is the conflict target —
$data must carry every PK column. Existence is resolved BY THE
DATABASE at write time: no prior SELECT, no insert-or-update guess.

Returns raw row(s) for backfill, same contract as insertOne
(RETURNING * when unset non-PK columns should refresh DB defaults).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$data` | array | - | column-name-keyed raw values (PK included) |

**➡️ Return value**

- Type: array


---

### deleteOne() · [source](../../src/Orm/Storage/Store.php#L68)

`public function deleteOne(string $class, array $id): void`

Delete one entity by PK values.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - | PK field => value |

**➡️ Return value**

- Type: void


---

### findBy() · [source](../../src/Orm/Storage/Store.php#L77)

`public function findBy(string $class, array $where): array`

Read raw rows. Returns plain assoc rows (no ResultSet).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | - | PK field => value, or field                          => value |

**➡️ Return value**

- Type: array


---

### findByPk() · [source](../../src/Orm/Storage/Store.php#L85)

`public function findByPk(string $class, array $id): array|null`

Read one raw row by PK values (null when missing).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$id` | array | - | PK field => value |

**➡️ Return value**

- Type: array|null


---

### count() · [source](../../src/Orm/Storage/Store.php#L91)

`public function count(string $class, array $where = []): int`

Count matching rows.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$class` | string | - |  |
| `$where` | array | `[]` | field => value |

**➡️ Return value**

- Type: int


---

### begin() · [source](../../src/Orm/Storage/Store.php#L102)

`public function begin(array|null $meta = null): void`

Open a transaction. $meta (the scheduled class's metadata) lets the
store pin to the class's write target when it supports per-class
routing — flush() pins the tx to the FIRST scheduled class's
writeRole instead of the constructor default (which made per-class
routing dead code on the EM path). Null = constructor default.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array\|null | `null` |  |

**➡️ Return value**

- Type: void


---

### commit() · [source](../../src/Orm/Storage/Store.php#L103)

`public function commit(): void`

**➡️ Return value**

- Type: void


---

### rollback() · [source](../../src/Orm/Storage/Store.php#L104)

`public function rollback(): void`

**➡️ Return value**

- Type: void


---

### inTransaction() · [source](../../src/Orm/Storage/Store.php#L109)

`public function inTransaction(): bool`

Whether a transaction (or savepoint level) is active.

**➡️ Return value**

- Type: bool


---

### enrichMetadata() · [source](../../src/Orm/Storage/Store.php#L119)

`public function enrichMetadata(array $meta, ReflectionClass $class): array`

Return $meta enriched (or throw for dishonorable attribute combos).

MUST stay JSON-serializable — the result feeds the L2 metadata cache.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$meta` | array | - | the freshly compiled generic metadata |
| `$class` | ReflectionClass | - | reflection of the compiled class |

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](README.md)
