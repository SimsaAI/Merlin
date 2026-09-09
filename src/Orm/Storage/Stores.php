<?php

namespace Azera\Orm\Storage;

/**
 * Context-attached store map: type name => Store instance. StoreManager's
 * replacement after the collapse — deliberately DUMB: no factories, no
 * roles, no default-role machinery. The type name is the single
 * discriminator (a connection-owning backend with two clients registers
 * two types: 'mongo-eu', 'mongo-us').
 *
 * WHY A HOLDER INSTEAD OF A PLAIN EM PROPERTY: Metadata::doCompile() is
 * STATIC and consults the registered store at compile time (enrichment —
 * e.g. pkMode must be known while PKs resolve). Reaching through
 * AppContext::instance() is the established static path; reaching for the
 * EM instance mid-compile would risk recursive construction.
 *
 * WHY NOT A STATIC ARRAY ON THE EM: tests isolate via a fresh AppContext
 * per test (setInstance/reset) — a process-global static would bleed
 * stores across tests into freshly compiled (and L2-cached) metadata.
 * A context-attached holder inherits that isolation for free.
 *
 * Registered in the context under this class name; EntityManager::setStore()
 * writes through it, storeFor()/Metadata read through it. Pure
 * configuration, no per-request state — deliberately NOT RequestScoped.
 */
class Stores
{
    /** @var array<string, Store> type name => store instance */
    protected array $map;

    public function __construct()
    {
        $this->map = [
            'sql' => new PdoStore(),
        ];
    }

    public function set(string $type, Store $store): void
    {
        $this->map[$type] = $store;
    }

    public function has(string $type): bool
    {
        return isset($this->map[$type]);
    }

    /**
     * Lenient NON-throwing lookup: null when the type is unregistered.
     * Hot paths treat "nothing registered" as ordinary control flow
     * (fallback resolution) — a miss costs one array probe.
     */
    public function tryGet(string $type): ?Store
    {
        return $this->map[$type] ?? null;
    }

}