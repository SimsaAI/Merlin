# Database Queries

**Master the query builder** - Deep dive into Merlin's powerful and intuitive query builder. Learn how to construct complex SELECT queries, perform joins, use subqueries, aggregate data, and leverage prepared statements for security.

Merlin uses a unified fluent query builder: `Merlin\Db\Query`.
You can access it directly via `Query::new()` or through models with `Model::query()`.

## Basic Setup

Before running queries, configure database connection(s) in your application context. This makes the database available throughout your application.

```php
use Merlin\AppContext;
use Merlin\Db\Database;

AppContext::instance()->dbManager()->set('default', new Database(
    'mysql:host=localhost;dbname=myapp',
    'user',
    'pass'
));
```

## Query Entry Points

You can build queries in two ways: directly using Query::new() for table-level operations, or through models for object-oriented workflows.

```php
use Merlin\Db\Query;

// Plain table
$q = Query::new()->table('users');

// From model
$users = User::query()->where('status', 'active')->select();
```

## SELECT

The query builder provides a fluent interface for constructing SELECT queries. Chain methods to add conditions, joins, sorting, and pagination. All queries use prepared statements for security.

```php
$users = Query::new()
    ->table('users', 'u')
    ->columns(['u.id', 'u.username', 'u.email'])
    ->where('u.created_at >', '2024-01-01')
    ->where('u.status', 'active')
    ->orderBy('u.created_at DESC')
    ->limit(20)
    ->offset(0)
    ->select();

$user = Query::new()
    ->table('users')
    ->where('id', 5)
    ->firstModel();
```

## WHERE Styles

Merlin supports three where clause styles to accommodate different preferences. All are equally safe and use prepared statements behind the scenes.

```php
// Condition + inline values (values are escaped and inserted into SQL)
User::query()->where('email = :email', ['email' => 'a@example.com'])->firstModel();

// Condition + bound parameters (values remain real PDO parameters)
User::query()->where('email = :email')->bind(['email' => 'a@example.com'])->firstModel();

// Column/value pair also inline (escaped and inserted into SQL)
User::query()->where('email', 'a@example.com')->firstModel();

// Column/value pair supporting operators
User::query()->where('email <>', 'a@example.com')->firstModel();
```

## FROM Subquery

Use a `Query` instance as the table source for a derived table. The subquery is wrapped in parentheses and its bind parameters are automatically merged into the parent query.

```php
use Merlin\Db\Query;

// Build the inner query independently
$recent = Query::new()
    ->table('orders')
    ->where('created_at > :since', ['since' => '2025-01-01'])
    ->columns(['user_id', 'total']);

// Use it as a derived table with an alias
$results = Query::new()
    ->from($recent, 'recent_orders')
    ->where('recent_orders.total >', 100)
    ->select();
// Produces: SELECT * FROM (SELECT `user_id`, `total` FROM `orders` WHERE ...) AS `recent_orders` WHERE ...
```

`from()` also accepts a plain table name string (same as `table()`):

```php
// Equivalent: plain string still works
$q = Query::new()->from('users', 'u')->where('u.status', 'active')->select();
```

## JOIN, GROUP, HAVING

Build complex queries with joins, aggregations, and grouping. The query builder makes it easy to construct sophisticated SQL while maintaining readability.

```php
$rows = Query::new()
    ->table('posts', 'p')
    ->columns([
        'p.id',
        'p.title',
        'u.username',
        'COUNT(c.id) AS comments_count',
    ])
    ->join('users', 'u', 'u.id = p.user_id')
    ->leftJoin('comments', 'c', 'c.post_id = p.id')
    ->where('p.status', 'published')
    ->groupBy('p.id')
    ->having('COUNT(c.id) > :min', ['min' => 0])
    ->orderBy('comments_count DESC')
    ->select();
```

### Subquery in JOIN

Any join method (`join`, `innerJoin`, `leftJoin`, `rightJoin`, `crossJoin`) also accepts a `Query` instance as the first argument. Supply an alias as the second argument so the outer query can reference it.

```php
// Pre-aggregate orders into a subquery
$orderTotals = Query::new()
    ->table('orders')
    ->where('status', 'completed')
    ->groupBy('user_id')
    ->columns(['user_id', 'SUM(total) AS total_spent']);

$results = Query::new()
    ->table('users', 'u')
    ->leftJoin($orderTotals, 'ot', 'ot.user_id = u.id')
    ->columns(['u.username', 'ot.total_spent'])
    ->where('ot.total_spent >', 500)
    ->select();
// Produces: SELECT ... FROM `users` AS `u`
//   LEFT JOIN (SELECT `user_id`, SUM(total) AS total_spent
//              FROM `orders` WHERE ... GROUP BY `user_id`) AS `ot` ON (ot.user_id = u.id)
//   WHERE ...
```

Bind parameters from the subquery are automatically propagated to the parent query — you never need to merge them manually.

## INSERT / UPSERT / UPDATE / DELETE

Beyond SELECT queries, the query builder handles all write operations. INSERT returns the new ID, UPDATE and DELETE return affected row counts for verification.

```php
// INSERT
$id = User::query()->insert([
    'username' => 'john',
    'email' => 'john@example.com',
]);

// INSERT with bound parameters
$id = User::query()->bind([
    'username' => 'john',
    'email' => 'john@example.com',
])->insert();

// UPSERT
User::query()->upsert([
    'id' => 1,
    'username' => 'john',
    'email' => 'john@example.com',
]);

// UPSERT with bound parameters
User::query()->bind([
    'id' => 1,
    'username' => 'john',
    'email' => 'john@example.com',
])->upsert();

// UPDATE
$affected = User::query()
    ->where('id', 1)
    ->update(['email' => 'john.new@example.com']);

// UPDATE with bound parameters
$affected = User::query()
    ->where('id', 1)
    ->bind(['email' => 'john.new@example.com'])
    ->update();

// DELETE
$deleted = User::query()
    ->where('status', 'inactive')
    ->delete();

// DELETE with bound parameters
$deleted = User::query()
    ->where('status = :status')
    ->bind(['status' => 'inactive'])
    ->delete();
```

## EXISTS / COUNT

```php
// Simple where with inline value
$exists = User::query()->where('email', 'john@example.com')->exists();
$total = User::query()->where('status', 'active')->tally();

// With bound parameters
$exists = User::query()->where('email = :email')->bind(['email' => 'john@example.com'])->exists();
$total = User::query()->where('status = :status')->bind(['status' => 'active'])->tally();
```

> **Note:** The query builder method is `tally()` (not `count()`) to avoid collisions with SQL aggregate columns named `count`. The static model helper `Model::tally()` works the same way.

```php
// Model-level tally
$active = User::tally(['status' => 'active']);
```

## Pagination with Paginator

Use `Merlin\Db\Paginator` to paginate any query builder. The paginator runs a count() query first, then fetches the requested page using `LIMIT/OFFSET`.

```php
$paginator = User::query()
    ->where('status', 'active')
    ->orderBy('created_at DESC')
    ->paginate(page: 2, pageSize: 20);

$items = $paginator->execute();

$meta = [
    'currentPage' => $paginator->getCurrentPage(),
    'totalPages' => $paginator->getTotalPages(),
    'totalItems' => $paginator->getTotalItems(),
    'firstItem' => $paginator->getFirstItemPos(),
    'lastItem' => $paginator->getLastItemPos(),
    'pageSize' => $paginator->getPageSize(),
];
```

You can enable reverse pagination using the third argument. It does not change your original ORDER BY. It only flips how pages are calculated, so page 1 returns the last items instead of the first ones.

```php
// Messages sorted oldest → newest
$messages = Query::new()
    ->table('messages')
    ->where('room_id', 15)
    ->orderBy('id ASC')
    ->paginate(page: 1, pageSize: 3, reverse: true)
    ->execute();
// Returns the LAST 3 messages, not the first 3.
```

## Sql::bind() — PDO-Bound Parameters in Expressions

Use `Sql::bind(name, value)` when you need a value to travel through as a **real PDO named parameter** (`:name`) instead of being inlined as an escaped literal. This is useful for values that must go through the PDO layer, e.g. for full-text vectors, JSON blobs, or binary data.

```php
use Merlin\Db\Sql;

// :qty is kept as a PDO placeholder; 5 is bound at execute time
User::query()
    ->where('id', Sql::bind('userId', 42))
    ->update(['stock' => Sql::raw('stock - :dec', ['dec' => 1])]);
```

Contrast with existing helpers:

| Helper | SQL output | Value delivery |
|---|---|---|
| `Sql::raw('x + :n', ['n'=>1])` | `x + 1` (literal) | Inlined (escaped) |
| `Sql::param('n')` | `:n` | Placeholder only — value must be supplied via `Query::bind()` |
| `Sql::bind('n', 1)` | `:n` | Placeholder **and** value bubbled as PDO param |

## Returning SQL Without Executing

```php
$sql = User::query()
    ->where('status', 'active')
    ->returnSql()
    ->select();
```

## Transactions

Use `Merlin\Db\Database` transaction methods:

```php
$db = AppContext::instance()->dbManager()->get('write');

$db->begin();
try {
    User::query()->insert(['username' => 'alice', 'email' => 'alice@example.com']);
    User::query()->where('id', 1)->update(['status' => 'active']);
    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}
```

## See Also

- [Models & ORM](04-MODELS-ORM.md)
- [Cookbook](10-COOKBOOK.md)
- [API Reference](api/index.md)
