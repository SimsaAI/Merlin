# Cookbook

**Practical solutions to common problems** - A collection of real-world recipes and patterns for everyday tasks like pagination, authentication, file uploads, API responses, caching, and more. Copy, adapt, and use in your projects.

Practical recipes built with the current Merlin API.

## 1) Paginated Listing

Pagination is essential for large datasets. Calculate offset from the page number and use limit/offset for efficient queries.

```php
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$users = User::query()
    ->where('status', 'active')
    ->orderBy('created_at DESC')
    ->limit($perPage)
    ->offset(($page - 1) * $perPage)
    ->select();

$total = User::query()->where('status', 'active')->count();
```

## 2) Find or Create

Atomically find an existing record or create it if it doesn't exist. Useful for ensuring unique constraints while avoiding race conditions.

```php
$user = User::firstOrCreate(
    ['email' => 'jane@example.com'],
    ['username' => 'jane']
);
```

## 3) Update or Create

Similar to find or create, but always updates the record with new data if it exists. Perfect for upsert operations.

```php
$user = User::updateOrCreate(
    ['email' => 'jane@example.com'],
    ['username' => 'jane.doe', 'status' => 'active']
);
```

## 4) Search by Dynamic Filters

Build flexible search queries that adapt based on which filters the user provides. Only add conditions for present filters to keep queries efficient.

```php
$query = User::query();

if (!empty($filters['email'])) {
    $query->where('email', $filters['email']);
}

if (!empty($filters['created_after'])) {
    $query->where('created_at >= :created_after', ['created_after' => $filters['created_after']]);
}

if (!empty($filters['roles'])) {
    $query->inWhere('role', $filters['roles']);
}

$rows = $query->orderBy('id DESC')->select();
```

## 5) Safe Bulk Update

Update multiple records that match a condition. Always use WHERE clauses to prevent accidentally modifying all rows.

```php
$affected = User::query()
    ->where('last_login < :cutoff', ['cutoff' => '2025-01-01'])
    ->update(['status' => 'inactive']);
```

## 6) Soft Delete Pattern

Instead of permanently deleting records, mark them as deleted with a timestamp. This allows recovery and maintains referential integrity.

```php
class Post extends \Merlin\Mvc\Model
{
    public int $id;
    public string $title;
    public ?string $deleted_at = null;

    public function softDelete(): bool
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save();
    }
}
```

## 7) Transaction with Multiple Writes

Wrap related database operations in a transaction to ensure data consistency. If any operation fails, all changes are rolled back.

```php
use Merlin\AppContext;

$db = AppContext::instance()->dbManager()->getDefault();

$db->begin();
try {
    $orderId = Order::query()->insert([
        'user_id' => 1,
        'status' => 'open',
    ]);

    OrderItem::query()->insert([
        'order_id' => $orderId,
        'product_id' => 2,
        'qty' => 3,
    ]);

    Product::query()
        ->where('id', 2)
        ->update(['stock' => new \Merlin\Db\Sql('stock - 3')]);

    $db->commit();
} catch (\Throwable $e) {
    $db->rollback();
    throw $e;
}
```

## 8) Read/Write Split

Distribute database load by routing reads to replicas and writes to the primary server. Merlin automatically uses the appropriate connection.

```php
use Merlin\AppContext;
use Merlin\Db\Database;

$ctx = AppContext::instance();
$ctx->dbManager()->set('write', new Database('mysql:host=primary;dbname=app', 'rw', 'secret'));
$ctx->dbManager()->set('read',  new Database('mysql:host=replica;dbname=app', 'ro', 'secret'));

$users = User::findAll(['status' => 'active']); // read

$user = User::find(1);
$user->status = 'inactive';
$user->save(); // write
```

## 9) Route + Dispatcher Integration

Connect routing to the dispatcher for a complete request handling flow. This is the core pattern of any Merlin web application.

```php
$router->add('GET', '/users/{id:int}', 'UserController::viewAction');
$route = $router->match('/users/7', 'GET');

if ($route !== null) {
    $response = $dispatcher->dispatch($route);
    $response->send();
}
```

## 10) CLI Cleanup Task

Create maintenance tasks for scheduled cleanup operations. Perfect for cron jobs that need to trim old data.

```php
class CleanupTask extends \Merlin\Cli\Task
{
    public function sessionsAction(int $days = 30): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $deleted = Session::query()
            ->where('last_seen < :cutoff', ['cutoff' => $cutoff])
            ->delete();

        echo "Deleted {$deleted} sessions\n";
    }
}
```

## 11) Subquery as Derived Table (FROM)

Use a `Query` instance as the `FROM` source to pre-aggregate or pre-filter data before the outer query processes it. Bind parameters from the subquery are automatically carried over — no manual merging required.

```php
use Merlin\Db\Query;

// Step 1 — build the inner query independently
$activeSales = Query::new()
    ->table('orders')
    ->where('status', 'completed')
    ->where('created_at > :since', ['since' => '2025-01-01'])
    ->groupBy('user_id')
    ->columns(['user_id', 'SUM(total) AS revenue']);

// Step 2 — wrap it as a derived table
$topCustomers = Query::new()
    ->from($activeSales, 'sales')   // alias required so outer query can reference columns
    ->where('sales.revenue >', 1000)
    ->orderBy('sales.revenue DESC')
    ->limit(10)
    ->select();
```

## 12) Subquery in JOIN

Join any pre-built `Query` directly. Works with `join()`, `leftJoin()`, `innerJoin()`, `rightJoin()`, and `crossJoin()`. Provide an alias as the second argument so the outer query can reference it in conditions and columns.

```php
use Merlin\Db\Query;

// Aggregate products to their latest price
$latestPrices = Query::new()
    ->table('price_history')
    ->where('effective_date <= :today', ['today' => date('Y-m-d')])
    ->groupBy('product_id')
    ->columns(['product_id', 'MAX(price) AS current_price']);

$catalogue = Query::new()
    ->table('products', 'p')
    ->leftJoin($latestPrices, 'lp', 'lp.product_id = p.id')
    ->columns(['p.name', 'p.sku', 'lp.current_price'])
    ->where('p.active', 1)
    ->orderBy('p.name')
    ->select();
```
