<?php

namespace Azera\Tests\Orm\Fixtures;

use Azera\Orm\Model;
use Azera\Orm\Attribute\Column;

/**
 * Exercises type inference from the PHP property type when #[Column]
 * omits `type:` — the attribute only overrides name/pk, not the type.
 */
class TypedColumns extends Model
{
    #[Column(pk: true)]
    public int $id;

    #[Column(name: 'status_code', pk: false)]
    public int $status;

    public float $score;

    public bool $active;

    public array $tags;

    public \DateTimeImmutable $created_at;

    public string $title;

    public $untyped;
}
