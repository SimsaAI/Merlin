# Models & ORM

**Work with database records as objects** - Discover Merlin's Active Record implementation for elegant database interactions. Learn about model relationships, composite keys, mass assignment, validation, and advanced querying techniques.

Merlin models use an Active Record style API backed by `Merlin\Db\Query`.

## Define a Model

Models are simple PHP classes that extend the base Model class. Define public properties for your table columns - Merlin handles the rest.

```php
<?php
namespace App\Models;

use Merlin\Mvc\Model;

class User extends Model
{
    public int $id;
    public string $username;
    public string $email;

    // Optional overrides:
    // public function source(): string { return 'users'; }
    // public function schema(): ?string { return 'public'; }
    // public function idFields(): array { return ['id']; }
}
```

By default, Merlin converts model class names to table names using snake_case (e.g., `User` → `user`, `AdminUser` → `admin_user`). To enable automatic pluralization of table names (e.g., `User` → `users`, `AdminUser` → `admin_users`), use the `ModelMapping` utility:

```php
use Merlin\Mvc\ModelMapping;

// Enable pluralization globally
ModelMapping::usePluralTableNames(true);
```

When enabled, the last word segment of the table name is pluralized (with support for irregular plurals like `person` → `people`). You can still override the source table per-model using the `source()` method if needed.

## Query Access

Every model provides direct access to the query builder, allowing you to construct complex queries while maintaining the model context.

```php
$activeUsers = User::query()
    ->where('status', 'active')
    ->orderBy('created_at DESC')
    ->limit(20)
    ->select();
```

## Load Helpers

Merlin provides convenient static methods for common retrieval patterns. These methods return fully hydrated model instances with state tracking enabled, making it easy to work with your data.

```php
$user = User::find(123);
$user = User::findOne(['email' => 'john@example.com']);
$users = User::findAll(['status' => 'active']);

$exists = User::exists(['email' => 'john@example.com']);
$count = User::count(['status' => 'active']);
```

Composite key lookup:

```php
$item = UserProduct::find([10, 25]);
$item = UserProduct::find(['user_id' => 10, 'product_id' => 25]);
```

## Create / Update / Delete

Models support full CRUD operations with automatic state tracking. When you modify a loaded model, Merlin tracks which fields changed so updates only affect modified columns.

```php
// Create via static helper
$user = User::create([
    'username' => 'alice',
    'email' => 'alice@example.com',
]);

// Update loaded model
$user = User::find(123);
$user->email = 'alice.new@example.com';
$user->save();

// Delete
$user->delete();
```

## State Tracking

Every model instance tracks its state, allowing you to detect changes, revert modifications, or perform optimistic updates. This is particularly useful for forms and multi-step operations.

```php
$user = User::find(123);
$user->email = 'changed@example.com';

if ($user->hasChanged()) {
    $user->update();
}

$user->loadState(); // revert to last saved/loaded state
```

## Convenience Helpers

```php
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],
    ['username' => 'john']
);

$user = User::updateOrCreate(
    ['email' => 'john@example.com'],
    ['username' => 'johnny']
);
```

## Read/Write Connections

Database connections are managed by `DatabaseManager` using named **roles**. Register connections in your bootstrap:

```php
use Merlin\AppContext;
use Merlin\Db\Database;

$mgr = AppContext::instance()->dbManager();
// Direct instance
$mgr->set('write', new Database('mysql:host=primary;dbname=myapp', 'rw', 'secret'));
// Lazy factory example
$mgr->set('read',  fn() => new Database('mysql:host=replica;dbname=myapp', 'ro', 'secret'));
```

Models read from the `read` role and write to the `write` role by default, falling back to the registered default when a role is absent.

Per-model override using role names:

```php
// Both read and write to the same role
User::setDefaultRole('analytics');

// Fine-grained
User::setDefaultReadRole('replica');
User::setDefaultWriteRole('primary');
```

For a single-database setup, register one connection – models fall through to the default:

```php
AppContext::instance()->dbManager()->set('default', new Database(...));
```

## Using ModelMapping Without Model Classes

`ModelMapping` lets you query the database using logical model names without defining PHP model classes. This is useful for rapid prototyping, dynamic table mappings, or when you need query-builder convenience for tables that don't warrant a full Active Record class.

### Register a mapping

```php
use Merlin\Db\Query;
use Merlin\Mvc\ModelMapping;

$mapping = ModelMapping::fromArray([
    // simple: name => table
    'User'    => 'users',
    // explicit, no schema:
    'Product' => ['source' => 'products'],
    // explicit with schema:
    'Order'   => ['source' => 'orders', 'schema' => 'public'],
]);

Query::setModelMapping($mapping);
Query::useModels(true);
```

Once registered, use the logical name wherever `Query` accepts a table or model reference:

```php
$results = Query::new()
    ->table('User')
    ->where('status', 'active')
    ->select();

// Joins also use logical names
$results = Query::new()
    ->table('User')
    ->join('Order', Condition::new()->where('User.id = Order.user_id'))
    ->columns(['User.id', 'User.email', 'Order.total'])
    ->select();
```

### Auto-generated table names

Pass `true` as the value to let `ModelMapping` derive the table name automatically from the model name (snake_case, or pluralized when `usePluralTableNames` is enabled):

```php
ModelMapping::usePluralTableNames(true); // User → users, AdminUser → admin_users

$mapping = ModelMapping::fromArray([
    'User'    => true,  // auto: "users"
    'Product' => true,  // auto: "products"
]);

Query::setModelMapping($mapping);
Query::useModels(true);
```

### Fluent builder

Use the `add()` method to build mappings programmatically:

```php
$mapping = (new ModelMapping())
    ->add('User', 'users')
    ->add('Order', 'orders', 'public'); // third arg is the schema

Query::setModelMapping($mapping);
```

### Resetting

Pass `null` to remove the mapping and revert to normal model-class resolution:

```php
Query::setModelMapping(null);

// Or disable model mode entirely for literal table-name queries
Query::useModels(false);
```

> **Note:** `ModelMapping` only affects `Query`-level operations. The Active Record helpers (`User::find()`, `User::create()`, etc.) still require a PHP class that extends `Model`.

## Related

- [Database Queries](05-DATABASE-QUERIES.md)
- [Cookbook](10-COOKBOOK.md)
- [API Reference](api/index.md)
