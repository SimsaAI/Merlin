<?php

namespace Azera\Orm\Casting;

/**
 * 'datetime' cast: PHP DateTimeInterface <-> SQL DATETIME/TIMESTAMP text.
 *
 * Contract:
 * - encode: DateTimeInterface -> 'Y-m-d H:i:s' (the canonical SQL DATETIME
 *   literal — the exact string the write pipeline hard-coded before this
 *   cast existed, so wire format is unchanged). Null and pre-formatted
 *   strings pass through — encode is idempotent.
 * - decode: parseable datetime text -> DateTimeImmutable; null and
 *   non-string values pass through. Unparseable text THROWS — fail loud,
 *   it means the column was edited outside the ORM or holds a format the
 *   parser cannot read (same corruption policy as JsonCast/BoolCast).
 *
 * Immutable decode is the deliberate default: entities share the
 * request-scoped heap, and a mutable DateTime on a tracked entity could
 * be modified in place without any property reassignment, silently
 * drifting from the diff snapshot. Applications that want a different
 * shape (mutable DateTime, Carbon, a custom wire format, timezone
 * normalization) REPLACE the registration — the datetime shaping lives
 * in the registry precisely so it is user-overridable, not hard-coded:
 *
 *   Casts::register('datetime', new MyDateTimeCast());
 *
 * Register before the first Metadata::for()/hydration of the affected
 * class (or FastHydrator::clear() after) — the decode plan is compiled
 * per class. Write-side shaping (EntityManager::extractData) picks the
 * replacement up immediately.
 *
 * On mongo documents this cast is EXCLUDED by default (the store's
 * castExclusions — BSON maps DateTimeInterface to BSON dates itself);
 * #[Column(cast: true)] forces the SQL text shape onto a mongo column.
 *
 * Snapshot contract: the property holds the decoded DateTimeImmutable,
 * node->data holds the canonical string (encode(decode(raw)) round-trip)
 * — diff() compares stable scalars.
 */
final class DateTimeCast implements Cast
{
    /** The canonical SQL DATETIME wire format. */
    public const FORMAT = 'Y-m-d H:i:s';

    public function encode(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::FORMAT);
        }

        return $value; // null / pre-formatted string: idempotent pass-through
    }

    public function decode(mixed $value): mixed
    {
        if ($value === null || !\is_string($value)) {
            return $value;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new \RuntimeException(
                "Stored datetime is NOT parseable: '{$value}'"
            );
        }
    }
}