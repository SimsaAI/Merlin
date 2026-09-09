<?php

namespace Azera\Orm\Storage;

/**
 * Persistence-level seam between the ORM and any storage backend.
 *
 * Operations the EntityManager's write pipeline performs — NOT a query builder. SQL stores
 * implement it over a {@see \Azera\Db\Database}; Mongo over the
 * mongodb library. The per-situation write strategies (RETURNING matrix)
 * live in each backend. A model belongs to exactly one store, routed by
 * metadata `store` (#[Entity(store: 'name')]) — the registry key
 * EntityManager::setStore() maps to an instance. Third-party backends:
 * implement this interface, register under a name, annotate #[Entity].
 */
interface Store
{
    /**
     * Capability flag: TRUE when this store wants values passed through
     * RAW (no DateTime formatting, no cast encoding) — the backend owns
     * value mapping (mongo: the driver owns BSON encoding). FALSE for
     * SQL-shaped stores (DateTime -> 'Y-m-d H:i:s', cast->encode()).
     */
    public function wantsNativeValues(): bool;

    /**
     * Connection identity for the class described by $meta: two classes
     * sharing one txTarget share one transaction target in flush().
     * Borrowing stores (SQL) derive it from the write role; owning stores
     * return a constant token (their connection is fixed per instance).
     */
    public function txTarget(array $meta): string;
    /**
     * Persist one entity: INSERT or UPDATE (upsert when flagged).
     * Returns raw row(s) for backfill: ['row' => ?array, 'id' => int|string|null].
     *
     * @param array<string, mixed> $data column-name-keyed raw values
     * @return array{row: ?array, id: int|string|null}
     */
    public function insertOne(string $class, array $data): array;

    /**
     * Update one entity by PK values.
     *
     * @param array<string, mixed> $data    column-name-keyed changed values
     * @param array<string, mixed> $id      PK field => value
     * @return array{row: ?array, id: int|string|null}
     */
    public function updateOne(string $class, array $data, array $id): array;

    /**
     * Atomic UPSERT: INSERT ... ON CONFLICT DO UPDATE (SQL) / updateOne
     * with upsert:true (Mongo). The caller-set PK is the conflict target —
     * $data must carry every PK column. Existence is resolved BY THE
     * DATABASE at write time: no prior SELECT, no insert-or-update guess.
     * Returns raw row(s) for backfill, same contract as insertOne
     * (RETURNING * when unset non-PK columns should refresh DB defaults).
     *
     * @param array<string, mixed> $data column-name-keyed raw values (PK included)
     * @return array{row: ?array, id: int|string|null}
     */
    public function upsertOne(string $class, array $data): array;

    /**
     * Delete one entity by PK values.
     * @param array<string, mixed> $id PK field => value
     */
    public function deleteOne(string $class, array $id): void;

    /**
     * Read raw rows. Returns plain assoc rows (no ResultSet).
     *
     * @param class-string $class
     * @param array $where    PK field => value, or field                          => value
     * @return list<array<string, mixed>>
     */
    public function findBy(string $class, array $where): array;

    /**
     * Read one raw row by PK values (null when missing).
     *
     * @param class-string $class
     * @param array<string, mixed> $id PK field => value
     */
    public function findByPk(string $class, array $id): ?array;

    /**
     * Count matching rows.
     * @param array $where field => value
     */
    public function count(string $class, array $where = []): int;

    /* --------------------------------------------------- transactions */

    /**
     * Open a transaction. $meta (the scheduled class's metadata) lets the
     * store pin to the class's write target when it supports per-class
     * routing — flush() pins the tx to the FIRST scheduled class's
     * writeRole instead of the constructor default (which made per-class
     * routing dead code on the EM path). Null = constructor default.
     */
    public function begin(?array $meta = null): void;
    public function commit(): void;
    public function rollback(): void;

    /**
     * Whether a transaction (or savepoint level) is active.
     */
    public function inTransaction(): bool;

    /**
     * Return $meta enriched (or throw for dishonorable attribute combos).
     * MUST stay JSON-serializable — the result feeds the L2 metadata cache.
     *
     * @param array<string, mixed> $meta the freshly compiled generic metadata
     * @param \ReflectionClass<object> $class reflection of the compiled class
     * @return array<string, mixed>
     */
    public function enrichMetadata(array $meta, \ReflectionClass $class): array;
}
