<?php

namespace Azera\Orm\Casting;

use Azera\Orm\Casting\Cast;

/**
 * Scalar float cast — see IntCast for the shared rationale.
 *
 * @internal registry detail — registered as 'float'
 */
final class FloatCast implements Cast
{
    public function encode(mixed $value): mixed
    {
        return $value;
    }

    public function decode(mixed $value): mixed
    {
        return $value === null ? null : (float) $value;
    }
}