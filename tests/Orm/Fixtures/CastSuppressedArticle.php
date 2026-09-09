<?php

namespace Azera\Tests\Orm\Fixtures;

use Azera\Orm\Model;
use Azera\Orm\Attribute\Column;

/** SQL model with EXPLICIT cast suppression: #[Column(cast: false)] — the
 * 'json' column is managed as raw text (no encode on write, no decode on
 * read, no snapshot normalization). */
class CastSuppressedArticle extends Model
{
    #[Column(type: 'int')]
    public $id;

    #[Column(type: 'json', cast: false)]
    public $tags;
}