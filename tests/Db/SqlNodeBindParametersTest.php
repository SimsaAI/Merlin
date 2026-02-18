<?php
namespace Merlin\Tests\Db;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestDatabase.php';

use Merlin\Db\Sql;
use Merlin\Db\Query;
use Merlin\Mvc\Model;
use PHPUnit\Framework\TestCase;

/**
 * Test helper to expose protected properties
 */
class TestUpdateBuilder extends Query
{
    public function setTableName(string $tableName): void
    {
        $this->table = $tableName;
    }
}

class SqlBindParametersTest extends TestCase
{
    private TestDatabase $db;

    protected function setUp(): void
    {
        $this->db = new TestDatabase('mysql');
    }

    public function testRawWithoutBindParams(): void
    {
        $node = Sql::raw('view_count + 1');
        $this->assertEmpty($node->getBindParams());
    }

    public function testRawWithSingleBindParam(): void
    {
        $node = Sql::raw('stock - :qty', ['qty' => 5]);
        $this->assertEquals(['qty' => 5], $node->getBindParams());
    }

    public function testRawWithMultipleBindParams(): void
    {
        $node = Sql::raw(
            'CASE WHEN view_count > :threshold THEN :published ELSE :draft END',
            ['threshold' => 100, 'published' => 'published', 'draft' => 'draft']
        );
        $expected = ['threshold' => 100, 'published' => 'published', 'draft' => 'draft'];
        $this->assertEquals($expected, $node->getBindParams());
    }

    public function testUpdateBuilderExtractsBindParams(): void
    {
        $builder = new TestUpdateBuilder($this->db);
        $builder->setTableName('`posts`'); // Set table name directly
        $builder->set('view_count', Sql::raw('view_count + :increment', ['increment' => 10]))
            ->where('id = 5'); // Simple WHERE without bind params

        $sql = $builder->returnSql()->update();

        // Should have parameter from Sql
        $this->assertStringNotContainsString(':increment', $sql);
        $this->assertStringContainsString('+ 10', $sql);
    }

    public function testUpdateBuilderMultipleSqlParams(): void
    {
        $builder = new TestUpdateBuilder($this->db);
        $builder->setTableName('`posts`');
        $builder->set('view_count', Sql::raw('view_count + :bonus', ['bonus' => 50]))
            ->set('like_count', Sql::raw('like_count + :extra', ['extra' => 5]))
            ->where('status = ?', 'published');

        $sql = $builder->returnSql()->update();

        // Should have both Sql parameters
        $this->assertStringNotContainsString(':bonus', $sql);
        $this->assertStringNotContainsString(':extra', $sql);
        $this->assertStringContainsString('+ 50', $sql);
        $this->assertStringContainsString('+ 5', $sql);
    }

    public function testUpdateBuilderCaseExpression(): void
    {
        $builder = new TestUpdateBuilder($this->db);
        $builder->setTableName('`posts`');
        $builder->set('status', Sql::raw(
            'CASE WHEN view_count > :threshold THEN :published ELSE :draft END',
            ['threshold' => 75, 'published' => 'published', 'draft' => 'draft']
        ))
            ->where('id = 3');

        $sql = $builder->returnSql()->update();

        // Should have all three Sql parameters
        $this->assertStringNotContainsString(':threshold', $sql);
        $this->assertStringNotContainsString(':published', $sql);
        $this->assertStringNotContainsString(':draft', $sql);
        $this->assertStringContainsString('> 75', $sql);
    }

    public function testUpdateBuilderGreatestFunction(): void
    {
        $builder = new TestUpdateBuilder($this->db);
        $builder->setTableName('`products`');
        $builder->set('stock', Sql::raw('GREATEST(stock - :qty, :min)', ['qty' => 10, 'min' => 0]))
            ->where('id = 42');

        $sql = $builder->returnSql()->update();

        $this->assertStringNotContainsString(':qty', $sql);
        $this->assertStringNotContainsString(':min', $sql);
        $this->assertStringContainsString('- 10', $sql);
        $this->assertStringContainsString(', 0', $sql);
    }

    public function testSqlGenerationWithBindParams(): void
    {
        $builder = new TestUpdateBuilder($this->db);
        $builder->setTableName('`posts`');
        $builder->set('view_count', Sql::raw('view_count + :increment', ['increment' => 5]))
            ->where('id = 1');

        $sql = $builder->returnSql()->update();

        // SQL should contain the placeholder :increment
        $this->assertStringNotContainsString(':increment', $sql);
        $this->assertStringContainsString('view_count + 5', $sql);
    }

    public function testMultipleUpdatesWithSameSql(): void
    {
        // Create Sql once
        $increment = Sql::raw('view_count + :inc', ['inc' => 1]);

        // Use in two different builders
        $builder1 = new TestUpdateBuilder($this->db);
        $builder1->setTableName('`posts`');
        $builder1->set('view_count', $increment)
            ->where('id = 1');

        $builder2 = new TestUpdateBuilder($this->db);
        $builder2->setTableName('`posts`');
        $builder2->set('view_count', $increment)
            ->where('id = 2');

        // Generate SQL statements first to trigger parameter extraction
        $sql1 = $builder1->returnSql()->update();
        $sql2 = $builder2->returnSql()->update();

        // Both should have the Sql parameter
        $this->assertStringNotContainsString(':inc', $sql1);
        $this->assertStringNotContainsString(':inc', $sql2);
        $this->assertStringContainsString('+ 1', $sql1);
        $this->assertStringContainsString('+ 1', $sql2);
    }
}
