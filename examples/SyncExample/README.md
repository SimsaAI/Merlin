# SyncExample – Model Synchronisation Demo

This example demonstrates the **DB → PHP** model synchronisation feature of the Merlin framework.  
You set up a database, run the sync task, and the framework automatically populates your model classes with typed PHP properties derived from the live database schema.

---

## Directory Structure

```
SyncExample/
├── console.php          CLI entry point
├── bootstrap.php        Database connection setup
├── Models/
│   ├── User.php         Empty model shell → sync populates properties
│   ├── Post.php
│   └── Comment.php
└── sql/
    ├── sqlite.sql       SQLite table definitions (no server required)
    ├── mysql.sql        MySQL  table definitions
    └── postgresql.sql   PostgreSQL table definitions
```

---

## Quick Start (SQLite – no server required)

### 1. Create the database

```bash
cd examples/SyncExample

# Create the SQLite database from the SQL file
sqlite3 sync_example.sqlite < sql/sqlite.sql
```

### 2. Configure the connection

The default `bootstrap.php` is already configured for SQLite.  
No changes needed for the quick start.

### 3. Preview changes (dry-run)

```bash
php console.php sync all Models --dry-run
```

Expected output:

```
Dry-run: scanning 3 file(s) in Models …

[DRY-RUN] SyncExample\Models\User (users): +6 added
    • add    $id: int — Primary key
    • add    $email: string
    • add    $name: ?string
    • add    $status: string
    • add    $created_at: string
    • add    $updated_at: ?string

[DRY-RUN] SyncExample\Models\Post (posts): +6 added
    ...

[DRY-RUN] SyncExample\Models\Comment (comments): +5 added
    ...

Done. 3 model(s) with changes, 0 error(s).
```

> In dry-run mode **no files are modified**. This is the default and safe to run any time.

### 4. Apply changes

```bash
php console.php sync all Models --apply
```

The model files in `Models/` are now updated with the correct typed properties.  
Open `Models/User.php` to see the result — it will look similar to:

```php
class User extends Model
{
    // Properties will be added automatically by the sync task.
    public int $id;
    public string $email;
    public ?string $name;
    public string $status;
    public string $created_at;
    public ?string $updated_at;
}
```

### 5. Sync a single model

```bash
php console.php sync model Models/User.php --apply
```

---

## Using MySQL

### 1. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sync_example;"
mysql -u root -p sync_example < sql/mysql.sql
```

### 2. Configure the connection

Edit `bootstrap.php` — comment out the SQLite section and uncomment the MySQL section:

```php
$ctx->dbManager()->set('default', new Database(
    dsn:  'mysql:host=127.0.0.1;dbname=sync_example;charset=utf8mb4',
    user: 'root',
    pass: 'secret'
));
```

### 3. Run sync

```bash
php console.php sync all Models --apply
```

> **MySQL advantage:** Column and table comments from the SQL file are synced into  
> PHP docblocks automatically.

---

## Using PostgreSQL

### 1. Create the database

```bash
psql -U postgres -c "CREATE DATABASE sync_example;"
psql -U postgres -d sync_example -f sql/postgresql.sql
```

### 2. Configure the connection

Edit `bootstrap.php` — uncomment the PostgreSQL section:

```php
$ctx->dbManager()->set('default', new Database(
    dsn:  'pgsql:host=127.0.0.1;dbname=sync_example',
    user: 'postgres',
    pass: 'secret'
));
```

### 3. Run sync

```bash
php console.php sync all Models --apply
```

---

## Testing Incremental Sync

### Add a column (simulate schema evolution)

```sql
-- SQLite (run inside sqlite3 sync_example.sqlite):
ALTER TABLE users ADD COLUMN avatar_url TEXT;

-- MySQL:
ALTER TABLE `users` ADD COLUMN `avatar_url` VARCHAR(500) NULL COMMENT 'Profile picture URL';

-- PostgreSQL:
ALTER TABLE users ADD COLUMN avatar_url TEXT;
COMMENT ON COLUMN users.avatar_url IS 'Profile picture URL';
```

Re-run:

```bash
php console.php sync model Models/User.php --apply
```

`$avatar_url` (and the column comment as a docblock for MySQL/PG) is appended to `User.php`.

### Remove a column (simulate column removal)

Drop a column from the database, then re-run sync:

```bash
php console.php sync all Models --apply
```

Any PHP property that no longer has a matching column is marked `@deprecated` in its docblock rather than deleted — keeping your code safe.

---

## CLI Reference

```
php console.php sync all  <directory> [--apply] [--database=<role>]
php console.php sync model <file>      [--apply] [--database=<role>]
```

| Flag                | Description                                           |
| ------------------- | ----------------------------------------------------- |
| _(none)_            | Dry-run mode: preview changes without modifying files |
| `--apply`           | Apply changes and write updated model files to disk   |
| `--database=<role>` | Use a specific database role (default: `read`)        |

---

## How it works

1. **ModelParser** tokenises the PHP file and extracts class properties + metadata
2. **SchemaProvider** (MySQL / PostgreSQL / SQLite) introspects the live database schema for the model's table
3. **ModelDiff** computes the difference between PHP properties and DB columns:
   - Missing PHP property → `AddProperty` operation
   - Missing DB column → `RemoveProperty` (marks existing property `@deprecated`)
   - Type mismatch → `UpdatePropertyType`
   - Comment mismatch → `UpdatePropertyComment`
4. **CodeGenerator** applies the operations to the PHP source file using string manipulation and regex
5. **SyncRunner** orchestrates the pipeline and writes the result back to disk
