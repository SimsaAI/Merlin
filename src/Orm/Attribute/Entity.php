<?php

namespace Azera\Orm\Attribute;

/**
 * Class-level persistence configuration — the ONE routing/naming attribute.
 *
 * Declares which store type handles the class and where its data lives
 * (table/collection name + schema), declaratively instead of overriding
 * source()/schema(). Compiled into metadata, so the Store seam, the query
 * builder (via ModelResolver) and the model facade defaults all see it
 * with zero runtime cost.
 *
 * Store routing: the `store` argument is the registry key the EntityManager
 * resolves — `$em->setStore('mongo', new MongoStore(...))` +
 * `#[Entity(store: 'mongo')]`. A connection-owning backend served under
 * multiple clients registers one store type per client ('mongo-eu',
 * 'mongo-us'); the type name IS the discriminator (there is no role axis).
 *
 * Connection roles stay with #[Connection] (readRole/writeRole metadata),
 * consumed by borrowing stores (PdoStore) per class.
 *
 * Precedence: a source()/schema() override on the model still wins over
 * the attribute (dynamic > static); the attribute wins over the naming
 * convention.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Entity
{
    public function __construct(
        public ?string $name = null,
        public ?string $schema = null,
        public ?string $store = null,
    ) {}
}