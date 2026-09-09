<?php

namespace Azera\Tests\Orm\Fixtures;

use Azera\Orm\Model;
use Azera\Orm\Attribute\Column;
use Azera\Orm\Attribute\Entity;

/** Mongo document fixture. */
#[Entity(store: 'mongo', name: 'articles')]
class ArticleDocument extends Model
{
    #[Column(type: 'int')]
    public $_id;

    public $title;

    #[Column(type: 'json')]
    public $tags;
}