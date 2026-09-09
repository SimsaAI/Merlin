<?php

namespace Azera\Tests\Orm\Fixtures;

use Azera\Orm\Model;
use Azera\Orm\Attribute\Column;
use Azera\Orm\Attribute\Entity;

/** Mongo document fixture. */
#[Entity(store: 'mongo', name: 'articles')]
class ArticleDocument extends Model
{
    /** The PK: ObjectId string after insert — cast suppressed (an 'int'
     * decode would throw on the 24-hex backfill; mongo owns identity). */
    #[Column(cast: false)]
    public $_id;

    public $title;

    #[Column(type: 'json')]
    public $tags;
}

/** Mongo document with FORCED casting: #[Column(cast: true)] overrides the
 * store's castExclusions — tags stored as a JSON TEXT string, not BSON. */
#[Entity(store: 'mongo', name: 'casted_articles')]
class CastForcedDocument extends Model
{
    #[Column(cast: false)]
    public $_id;

    public $title;

    #[Column(type: 'json', cast: true)]
    public $tags;
}