<?php
namespace Merlin\Tests\Db;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestDatabase.php';

use Merlin\Db\Sql;
use Merlin\Db\Condition;
use PHPUnit\Framework\TestCase;


class ConditionBuilderTest extends TestCase
{
    public function testSimpleWhereEscapedLiteral(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->where('id', 123);
        $this->assertEquals('("id" = 123)', $cb->toSql());
    }

    public function testNamedPlaceholderReplacement(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->where('col = :v', ['v' => 'foo']);
        $this->assertEquals("(col = 'foo')", $cb->toSql());
    }

    public function testBetweenWhere(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->betweenWhere('age', 18, 30);
        $this->assertEquals('("age" BETWEEN 18 AND 30)', $cb->toSql());
    }

    public function testInWhereArray(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->inWhere('id', [1, 2, 3]);
        $this->assertEquals('("id" IN (1,2,3))', $cb->toSql());
    }

    public function testGroupingWithOr(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->where('a', 1)
            ->groupStart()
            ->where('b', 2)
            ->orWhere('c', 3)
            ->groupEnd();

        $this->assertEquals('("a" = 1) AND (("b" = 2) OR ("c" = 3))', $cb->toSql());
    }

    public function testConditionCreate(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->where('id', 123);
        $this->assertEquals('("id" = 123)', $cb->toSql());
    }

    public function testQualifiedIdentifiers(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->where('users.id', 123);
        $this->assertEquals('("users"."id" = 123)', $cb->toSql());
    }

    public function testLikeWithQualifiedIdentifier(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->likeWhere('u.name', 'john%');
        $this->assertEquals('("u"."name" LIKE \'john%\')', $cb->toSql());
    }

    public function testBetweenWithQualifiedIdentifier(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->betweenWhere('u.age', 18, 30);
        $this->assertEquals('("u"."age" BETWEEN 18 AND 30)', $cb->toSql());
    }

    public function testInWithQualifiedIdentifier(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->inWhere('u.status', [1, 2, 3]);
        $this->assertEquals('("u"."status" IN (1,2,3))', $cb->toSql());
    }

    public function testLargeInList(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        // Test with a large list (2000 items) - this would have caused PCRE failures before
        $largeList = range(1, 2000);
        $cb->inWhere('id', $largeList);

        $result = $cb->toSql();
        $this->assertStringContainsString('("id" IN (', $result);
        $this->assertStringContainsString('2000', $result);
    }

    public function testPlainConditionStringWithEquals(): void
    {
        $db = new TestPgDatabase();
        $cb = Condition::new($db);

        $cb->where('u.id = o.user_id');
        $this->assertEquals('("u"."id" = "o"."user_id")', $cb->toSql());
    }

    public function testHavingWithSqlPlaceholder(): void
    {
        $db = new TestPgDatabase();
        $cb = new Condition($db);

        $cb->having(
            ':count > :min_orders',
            [
                'count' => Sql::func('COUNT', [Sql::column('o.id')]),
                'min_orders' => 5
            ]
        );

        $this->assertEquals('(HAVING COUNT("o"."id") > 5)', $cb->toSql());
    }
}
