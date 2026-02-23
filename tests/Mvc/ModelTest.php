<?php
namespace Merlin\Tests\Mvc;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Db/TestDatabase.php';

use Merlin\AppContext;
use Merlin\Tests\Db\TestPgDatabase;
use PHPUnit\Framework\TestCase;

class DummyModel extends \Merlin\Mvc\Model
{
    public $id;
    public $name;
    public $_internal;

    public function modelIdFields(): array
    {
        return ['id'];
    }
}

class ModelTest extends TestCase
{
    public function testStateSaveLoadAndHasChanged(): void
    {
        $db = new TestPgDatabase();
        AppContext::instance()->dbManager()->set('default', $db);

        $m = new DummyModel();
        $m->id = null;
        $m->name = 'Alice';
        $m->_internal = 'secret';

        $m->saveState();
        $this->assertFalse($m->hasChanged());

        $m->name = 'Bob';
        $this->assertTrue($m->hasChanged());

        $m->loadState();
        $this->assertEquals('Alice', $m->name);
        $this->assertFalse($m->hasChanged());
    }

    public function testCreatePopulatesIdAndUpdatesState(): void
    {
        $db = new TestPgDatabase();
        AppContext::instance()->dbManager()->set('default', $db);
        // Simulate DB returning the inserted row
        $db->setMockResults([
            [
                ['id' => 123, 'name' => 'Charlie']
            ]
        ]);

        $m = new DummyModel();
        $m->name = 'Charlie';

        $this->assertTrue($m->insert());
        $this->assertEquals(123, $m->id);

        $state = $m->getState();
        $this->assertNotNull($state);
        $this->assertEquals(123, $state->id);
        $this->assertEquals('Charlie', $state->name);
    }

    public function testUpdateExecutesAndClearsChanges(): void
    {
        $db = new TestPgDatabase();
        AppContext::instance()->dbManager()->set('default', $db);
        // Create the model first so a state exists, then change and update
        $db->setMockResults([
            [
                ['id' => 5, 'name' => 'Delta']
            ]
        ]);

        $m = new DummyModel();
        $m->name = 'Delta';
        $this->assertTrue($m->insert());

        $m->name = 'Delta2';
        $db->clearQueries();

        $result = $m->update();
        $this->assertTrue($result);
        $this->assertNotEmpty($db->queries, 'Update should execute queries on the driver');
        $this->assertFalse($m->hasChanged(), 'State should be updated after update()');
    }
}
