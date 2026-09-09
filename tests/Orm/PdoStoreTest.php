<?php

namespace Azera\Tests\Orm;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Db/TestDatabase.php';
require_once __DIR__ . '/Fixtures/Article.php';

use Azera\AppContext;
use Azera\Orm\Metadata;
use Azera\Orm\Storage\PdoStore;
use Azera\Orm\Storage\Stores;
use Azera\Tests\Db\TestDatabase;
use Azera\Tests\Orm\Fixtures\Article;
use Azera\Tests\Orm\Fixtures\InventoryItem;
use PHPUnit\Framework\TestCase;

class PdoStoreTest extends TestCase
{
    private TestDatabase $db;
    private PdoStore $store;

    protected function setUp(): void
    {
        AppContext::setInstance(new AppContext());
        Metadata::clear();
        $this->db = new TestDatabase('pgsql');
        AppContext::instance()->dbManager()->set('read', $this->db);
        AppContext::instance()->dbManager()->set('write', $this->db);
        $this->store = new PdoStore(AppContext::instance()->dbManager(), 'read', 'write');
    }

    public function testInsertPkSetPlainInsert(): void
    {
        $this->store->insertOne(Article::class, ['id' => 42, 'title' => 'T', 'created_at' => null, 'status_code' => null]);

        $q = $this->dataQueries();
        $this->assertCount(1, $q);
        $this->assertStringStartsWith('INSERT INTO "article"', $q[0]['sql']);
        $this->assertStringNotContainsString('RETURNING', $q[0]['sql']);
    }

    public function testInsertAutoIncrementReturningId(): void
    {
        $this->db->setMockResults([[['id' => 5]]]);
        $result = $this->store->insertOne(Article::class, ['id' => null, 'title' => 'T', 'created_at' => '2026-01-01', 'status_code' => 1]);

        $q = $this->dataQueries();
        $this->assertStringContainsString('RETURNING "id"', $q[0]['sql']);
        $this->assertSame(5, $result['id']);
    }

    public function testFindByReturnsRawRows(): void
    {
        $this->db->setMockResults([[['id' => 1, 'title' => 'A']]]);

        $rows = $this->store->findBy(Article::class, ['id' => 1]);

        $this->assertSame([['id' => 1, 'title' => 'A']], $rows);
        $this->assertStringStartsWith('SELECT * FROM "article"', $this->lastDataSql());
    }

    public function testSchemaQualifiedTableInAllSql(): void
    {
        // #[Entity(schema: 'warehouse', name: 'inventory_items')] — every
        // statement targets "warehouse"."inventory_items".
        $this->db->setMockResults([[['cnt' => 3]]]);

        $this->store->count(InventoryItem::class, ['tenant_id' => 1]);

        $sql = $this->lastDataSql();
        $this->assertStringStartsWith(
            'SELECT COUNT(*) AS cnt FROM "warehouse"."inventory_items"',
            $sql
        );

        // INSERT / UPDATE / DELETE paths use the same qualification.
        $this->db->clearQueries();
        $this->store->insertOne(InventoryItem::class, ['tenant_id' => 1, 'item_id' => 2, 'name' => 'X']);
        $this->assertStringStartsWith(
            'INSERT INTO "warehouse"."inventory_items"',
            $this->dataQueries()[0]['sql']
        );

        $this->db->clearQueries();
        $this->store->updateOne(InventoryItem::class, ['name' => 'Y'], ['tenant_id' => 1, 'item_id' => 2]);
        $this->assertStringStartsWith(
            'UPDATE "warehouse"."inventory_items"',
            $this->dataQueries()[0]['sql']
        );

        $this->db->clearQueries();
        $this->store->deleteOne(InventoryItem::class, ['tenant_id' => 1, 'item_id' => 2]);
        $this->assertStringStartsWith(
            'DELETE FROM "warehouse"."inventory_items"',
            $this->dataQueries()[0]['sql']
        );
    }

    public function testCount(): void
    {
        $this->db->setMockResults([[['cnt' => 7]]]);

        $this->assertSame(7, $this->store->count(Article::class));
    }

    /* ============================================== tx map (per-target txs)
     *
     * One PdoStore instance holds one tx PER DISTINCT write connection:
     * begin($meta) opens (or joins) the tx on $meta's write role; two
     * roles aliasing the SAME Database share one tx (no savepoint);
     * commit/rollback($meta) address exactly that target's store-begun tx
     * and never touch caller-opened ones. */

    public function testTwoWriteTargetsHoldIndependentTransactions(): void
    {
        $primary = new TestDatabase('pgsql');
        $replica = new TestDatabase('pgsql');
        $dbm     = AppContext::instance()->dbManager();
        $dbm->set('primary', $primary);
        $dbm->set('secondary', $replica);

        $metaPrimary = ['writeRole' => 'primary', 'readRole' => 'primary'];
        $metaSecond  = ['writeRole' => 'secondary', 'readRole' => 'secondary'];

        $this->store->begin($metaPrimary);
        $this->store->begin($metaSecond);

        // Independent: BEGIN on both connections, each in its own tx.
        $this->assertTrue($primary->inTransaction());
        $this->assertTrue($replica->inTransaction());
        $this->assertTrue($this->store->inTransaction($metaPrimary));
        $this->assertTrue($this->store->inTransaction($metaSecond));
        $this->assertTrue($this->store->inTransaction());

        // Writes route to each target's OWN connection.
        $this->store->insertOne(Article::class, ['id' => 1, 'title' => 'A', 'created_at' => null, 'status_code' => null]);
        $this->store->insertOne(Article::class, ['id' => 2, 'title' => 'B', 'created_at' => null, 'status_code' => null]);
        $this->assertCount(1, $primary->queries);
        $this->assertCount(1, $replica->queries);

        // Committing ONE target leaves the other open.
        $this->store->commit($metaPrimary);
        $this->assertFalse($primary->inTransaction());
        $this->assertTrue($replica->inTransaction());
    }

    public function testSameConnectionTargetsShareOneTransaction(): void
    {
        // Two write roles aliasing the SAME Database: begin() joins — one
        // BEGIN, one tx, and a single commit finalizes both.
        $dbm = AppContext::instance()->dbManager();
        $dbm->set('a', $this->db);
        $dbm->set('b', $this->db);

        $this->store->begin(['writeRole' => 'a']);
        $this->store->begin(['writeRole' => 'b']);

        $this->assertSame(1, count(array_filter($this->db->queries, fn($q) => $q['sql'] === 'BEGIN')));
        $this->assertTrue($this->store->inTransaction(['writeRole' => 'b']));

        $this->store->commit(['writeRole' => 'b']);
        $this->assertFalse($this->db->inTransaction());
        $this->assertSame(1, count(array_filter($this->db->queries, fn($q) => $q['sql'] === 'COMMIT')));
    }

    public function testCallerOpenedTxIsJoinedButNeverCommittedOrRolledBack(): void
    {
        // A caller began a tx directly on the write connection.
        $this->db->begin();

        $this->assertTrue($this->store->inTransaction(['writeRole' => 'write']));

        // begin() must NOT open a savepoint/second BEGIN over it…
        $this->store->begin(['writeRole' => 'write']);
        $this->assertSame(1, count(array_filter($this->db->queries, fn($q) => $q['sql'] === 'BEGIN')));

        // …and the store must not commit/rollback the caller's tx.
        $this->store->commit(['writeRole' => 'write']);
        $this->store->rollback(['writeRole' => 'write']);
        $this->assertTrue($this->db->inTransaction());

        $this->db->commit(); // caller unwinds its own tx
    }

    public function testBareCommitFinalizesAllStoreBegunTransactions(): void
    {
        $primary = new TestDatabase('pgsql');
        $dbm     = AppContext::instance()->dbManager();
        $dbm->set('primary', $primary);

        $this->store->begin(['writeRole' => 'write']);
        $this->store->begin(['writeRole' => 'primary']);

        $this->store->commit();

        $this->assertFalse($this->db->inTransaction());
        $this->assertFalse($primary->inTransaction());
        $this->assertSame(1, count(array_filter($primary->queries, fn($q) => $q['sql'] === 'COMMIT')));
    }

    public function testReadRoutesToItsOwnRolesNotAnotherTargetsTx(): void
    {
        $read = new TestDatabase('pgsql');
        $tx   = new TestDatabase('pgsql');
        $dbm  = AppContext::instance()->dbManager();
        $dbm->set('replica', $read);
        $dbm->set('primary', $tx);

        // A tx is open on 'primary' (another target's tx).
        $this->store->begin(['writeRole' => 'primary']);

        $read->setMockResults([[['id' => 1, 'title' => 'A']]]);

        // A read resolving to 'replica' (no tx there) runs autocommit on
        // the replica — it is NOT hijacked into the open tx's connection.
        // InventoryItem carries #[Connection(read: 'replica', write: 'primary')].
        $rows = $this->store->findBy(InventoryItem::class, ['tenant_id' => 1]);

        $this->assertSame([['id' => 1, 'title' => 'A']], $rows);
        $this->assertCount(1, $read->queries);
        $this->assertCount(1, $tx->queries); // only the BEGIN
    }

    private function dataQueries(): array
    {
        return array_values(array_filter($this->db->queries, fn($q) => !in_array($q['sql'], ['BEGIN', 'COMMIT', 'ROLLBACK'], true)));
    }

    private function lastDataSql(): ?string
    {
        $q = $this->dataQueries();
        return $q === [] ? null : end($q)['sql'];
    }
}