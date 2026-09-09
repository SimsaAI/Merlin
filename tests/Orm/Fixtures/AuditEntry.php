<?php

namespace Azera\Tests\Orm\Fixtures;

use Azera\Orm\Model;
use Azera\Orm\Attribute\Column;
use Azera\Orm\Attribute\Connection;

/** Fixture: audit table on a DEDICATED write role (multi-connection write set). */
#[Connection(write: 'primary', read: 'primary')]
class AuditEntry extends Model
{
    #[Column(type: 'int')]
    public $id;

    public $title;
}