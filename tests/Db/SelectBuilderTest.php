<?php
namespace Merlin\Tests\Db;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestDatabase.php';

use Merlin\Db\Sql;
use Merlin\Db\Query;
use Merlin\AppContext;
use Merlin\Db\Condition;
use Merlin\Mvc\ModelMapping;
use PHPUnit\Framework\TestCase;

class SelectBuilderTest extends TestCase
{
    public function testBasicSelectWithJoinGroupOrderLimit(): void
    {
        // Disable model lookup to use plain table names
        Query::useModels(false);

        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;
        $sb = new Query($db);

        // Use Condition for JOIN to get identifier protection
        $joinCondition = Condition::new($db)->where('o.user_id = u.id');

        $sb->table('users u')
            ->columns(['u.id', 'u.name'])
            ->leftJoin('orders o', $joinCondition)
            ->where('u.id', 1)
            ->orderBy('u.name')
            ->limit(10, 5)
            ->sharedLock(true);

        $expected = 'SELECT "u"."id", "u"."name" FROM "users" AS "u" LEFT JOIN "orders" AS "o" ON (("o"."user_id" = "u"."id")) WHERE ("u"."id" = 1) ORDER BY "u"."name" LIMIT 10 OFFSET 5 FOR SHARE';

        $this->assertEquals($expected, $sb->returnSql()->select());
    }

    public function testConditionResolvesModelToTableAlias(): void
    {
        Query::useModels(true);
        Query::setModelMapping(new ModelMapping([
            'Model' => ['source' => 'user', 'schema' => null],
        ]));

        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;
        $sb = new Query($db);

        $sb->table('Model')
            ->columns(['Model.id', 'Model.name']);

        $c = Condition::new()
            ->where('Model.age >=', 18)
            ->where('Model.status', 'active')
            ->groupStart()
            ->where('Model.role', 'admin')
            ->orWhere('Model.role', 'moderator')
            ->groupEnd();

        $sb->where($c);

        $expected = 'SELECT "user"."id", "user"."name" FROM "user" WHERE (("user"."age" >= 18) AND ("user"."status" = \'active\') AND (("user"."role" = \'admin\') OR ("user"."role" = \'moderator\')))';

        $this->assertEquals($expected, $sb->returnSql()->select());
    }

    public function testModelColumnResolutionWithJoinsAndSqlComposition(): void
    {
        // Test the documentation example: Model.column notation in SelectBuilder with JOINs and Sql composition
        // This is crucial: verifies Model.column resolves to correct table identifiers throughout the query

        Query::useModels(true);
        Query::setModelMapping(new ModelMapping([
            'Order' => ['source' => 'order', 'schema' => 'public'],
            'Customer' => ['source' => 'customer', 'schema' => 'public'],
        ]));

        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;
        $sb = new Query($db);

        // Build the query from documentation:
        // $results = Order::selectBuilder()
        //     ->join(Customer::class, 'Customer.id = Order.customer_id')
        //     ->columns([
        //         'Order.id',
        //         Sql::concat(
        //             Sql::column('Customer.first_name'),
        //             Sql::value(' '),
        //             Sql::column('Customer.last_name')
        //         )->as('customer_name'),
        //         Sql::column('Order.total')
        //     ])
        //     ->execute();

        $customerName = Sql::concat(
            Sql::column('Customer.first_name'),
            Sql::value(' '),
            Sql::column('Customer.last_name')
        )->as('customer_name');

        $sb->table('Order')
            ->join('Customer', 'Customer.id = Order.customer_id')
            ->columns([
                'Order.id',
                $customerName,
                'Order.total'
            ]);

        $sql = $sb->returnSql()->select();

        // Verify complete expected SQL structure
        // SELECT o.id, concatenation, o.total FROM order AS o JOIN customer AS c ON ...
        $expected = 'SELECT "order"."id", "customer"."first_name" || \' \' || "customer"."last_name" AS "customer_name", "order"."total" FROM "public"."order" JOIN "public"."customer" ON ("customer"."id" = "order"."customer_id")';

        $this->assertEquals($expected, $sql);

        Query::setModelMapping(null);
    }

    public function testReusableConditionResolvesPerQueryContext(): void
    {
        Query::useModels(true);
        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;

        $reusable = Condition::new()
            ->where('Model.id', 1)
            ->where('Model.status', 'active');

        Query::setModelMapping(new ModelMapping([
            'Model' => ['source' => 'users', 'schema' => null],
        ]));

        $first = Query::new($db)
            ->table('Model')
            ->where($reusable)
            ->returnSql()
            ->select();

        $this->assertEquals(
            'SELECT * FROM "users" WHERE (("users"."id" = 1) AND ("users"."status" = \'active\'))',
            $first
        );

        Query::setModelMapping(new ModelMapping([
            'Model' => ['source' => 'accounts', 'schema' => null],
        ]));

        $second = Query::new($db)
            ->table('Model')
            ->where($reusable)
            ->returnSql()
            ->select();

        Query::setModelMapping(null);

        $this->assertEquals(
            'SELECT * FROM "accounts" WHERE (("accounts"."id" = 1) AND ("accounts"."status" = \'active\'))',
            $second
        );
    }

    public function testReusableJoinConditionResolvesPerModelMapping(): void
    {
        Query::useModels(true);
        Query::setModelMapping(new ModelMapping([
            'User' => ['source' => 'users', 'schema' => null],
            'Order' => ['source' => 'orders', 'schema' => null],
        ]));
        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;

        $joinCondition = Condition::new()->where('User.id = Order.user_id');

        $first = Query::new($db)
            ->table('User')
            ->join('Order', $joinCondition)
            ->returnSql()
            ->select();

        $this->assertEquals(
            'SELECT * FROM "users" JOIN "orders" ON (("users"."id" = "orders"."user_id"))',
            $first
        );

        Query::setModelMapping(new ModelMapping([
            'User' => ['source' => 'accounts', 'schema' => null],
            'Order' => ['source' => 'purchases', 'schema' => null],
        ]));

        $second = Query::new($db)
            ->table('User')
            ->join('Order', $joinCondition)
            ->returnSql()
            ->select();

        Query::setModelMapping(null);

        $this->assertEquals(
            'SELECT * FROM "accounts" JOIN "purchases" ON (("accounts"."id" = "purchases"."user_id"))',
            $second
        );
    }

    public function testReusableBoundConditionResolvesPerModelMapping(): void
    {
        Query::useModels(true);
        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;

        $bound = Condition::new()
            ->where('Model.status = :status')
            ->bind(['status' => 'active']);

        Query::setModelMapping(new ModelMapping([
            'Model' => ['source' => 'users', 'schema' => null],
        ]));

        $first = Query::new($db)
            ->table('Model')
            ->where($bound)
            ->returnSql()
            ->select();

        $this->assertEquals(
            'SELECT * FROM "users" WHERE (("users"."status" = \'active\'))',
            $first
        );

        Query::setModelMapping(new ModelMapping([
            'Model' => ['source' => 'accounts', 'schema' => null],
        ]));

        $second = Query::new($db)
            ->table('Model')
            ->where($bound)
            ->returnSql()
            ->select();

        Query::setModelMapping(null);

        $this->assertEquals(
            'SELECT * FROM "accounts" WHERE (("accounts"."status" = \'active\'))',
            $second
        );
    }

    public function testReusableBoundJoinConditionResolvesPerModelMapping(): void
    {
        Query::useModels(true);
        $db = new TestPgDatabase();
        AppContext::instance()->db = $db;

        $joinCondition = Condition::new()
            ->where('User.id = Order.user_id')
            ->where('Order.state = :state')
            ->bind(['state' => 'open']);

        Query::setModelMapping(new ModelMapping([
            'User' => ['source' => 'users', 'schema' => null],
            'Order' => ['source' => 'orders', 'schema' => null],
        ]));

        $first = Query::new($db)
            ->table('User')
            ->join('Order', $joinCondition)
            ->returnSql()
            ->select();

        $this->assertEquals(
            'SELECT * FROM "users" JOIN "orders" ON (("users"."id" = "orders"."user_id") AND ("orders"."state" = \'open\'))',
            $first
        );

        Query::setModelMapping(new ModelMapping([
            'User' => ['source' => 'accounts', 'schema' => null],
            'Order' => ['source' => 'purchases', 'schema' => null],
        ]));

        $second = Query::new($db)
            ->table('User')
            ->join('Order', $joinCondition)
            ->returnSql()
            ->select();

        Query::setModelMapping(null);

        $this->assertEquals(
            'SELECT * FROM "accounts" JOIN "purchases" ON (("accounts"."id" = "purchases"."user_id") AND ("purchases"."state" = \'open\'))',
            $second
        );
    }
}
