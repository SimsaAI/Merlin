# Database Queries

**Master the query builder** - Deep dive into Merlin's powerful and intuitive query builder. Learn how to construct complex SELECT queries, perform joins, use subqueries, aggregate data, and leverage prepared statements for security.

Merlin uses a unified fluent query builder: `Merlin\Db\Query`.
You can access it directly via `Query::new()` or through models with `Model::query()`.

## Basic Setup

Before running queries, configure database connection(s) in your application context. This makes the database available throughout your application.

```php
use Merlin\AppContext;
use Merlin\Db\Database;

AppContext::instance()->db = new Database(
    'mysql:host=localhost;dbname=myapp',
    'user',
    'pass'
);
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
    ->where('u.status', 'active')
    ->orderBy('u.created_at DESC')
    ->limit(20)
    ->offset(0)
    ->select();

$user = Query::new()
    ->table('users')
    ->where('id', 5)
    ->first();
```

## WHERE Styles

Merlin supports two where clause styles to accommodate different preferences. Both are equally safe and use prepared statements behind the scenes.

Both styles are supported:

```php
// Condition + bind params (Phalcon style)
User::query()->where('email = :email', ['email' => 'a@example.com'])->first();

// Column/value (CodeIgniter style)
User::query()->where('email', 'a@example.com')->first();
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

## INSERT / UPSERT / UPDATE / DELETE

Beyond SELECT queries, the query builder handles all write operations. INSERT returns the new ID, UPDATE and DELETE return affected row counts for verification.

```php
// INSERT
$id = User::query()->insert([
    'username' => 'john',
    'email' => 'john@example.com',
]);

// UPSERT
User::query()->upsert([
    'id' => 1,
    'username' => 'john',
    'email' => 'john@example.com',
]);

// UPDATE
$affected = User::query()
    ->where('id', 1)
    ->update(['email' => 'john.new@example.com']);

// DELETE
$deleted = User::query()
    ->where('status', 'inactive')
    ->delete();
```

## EXISTS / COUNT

```php
$exists = User::query()->where('email', 'john@example.com')->exists();
$total = User::query()->where('status', 'active')->count();
```

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
$db = AppContext::instance()->getWriteDb();

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
- [API Reference](11-API-REFERENCE.md)
