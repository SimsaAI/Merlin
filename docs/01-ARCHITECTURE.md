# Architecture

**Understanding Merlin's design** - Learn how Merlin's core components fit together, from the AppContext service container to the MVC layer, database abstraction, and CLI tools. This guide explains the framework's architectural principles and design decisions.

This document outlines how core Merlin components work together.

![Merlin Overall Architecture](images/architecture-overview.svg)

## Core Principles

- Lightweight runtime with minimal mandatory dependencies
- Explicit routing and dispatch
- Unified query API for model and table workflows
- Read/write DB separation support
- Simple composition through `AppContext`

## Main Components

Merlin is organized into distinct layers, each handling a specific concern. Understanding these components helps you leverage the framework effectively.

### `AppContext`

Central runtime context for shared services:

- `db`, `dbRead`, `dbWrite` - Database connections
- `request` - HTTP request object
- `view` - View engine instance
- `session` - Session manager
- `cookies` - Cookie manager
- `routing` - Current route information (populated by Dispatcher)

It is a singleton via `AppContext::instance()`.

### MVC Layer

- `Router` matches URI + method to route patterns, extracting parameters
- `Dispatcher` resolves controllers, executes middleware pipeline, invokes actions, and stores route info in `AppContext->route`
- `Controller` provides access to request/context plus lifecycle hooks
- `ViewEngine` renders templates, layouts, and namespaced views
- `RoutingResult` (in `AppContext->route`) contains resolved route information accessible anywhere

### Data Layer

- `Model` provides Active Record style methods and state tracking
- `Query` is the fluent SQL builder for select/write/count/exists
- `Database` wraps PDO and transaction helpers
- `ResultSet` provides iterable model/row access

### CLI Layer

- `Console` maps CLI args to `Task` classes and `*Action()` methods
- `Task` is the base class for commands

## Request Flow (Web)

Understanding the request lifecycle helps you know where to hook in custom logic. Each request follows a clear path from router to controller to response.

```text
HTTP Request
  -> Router::match()
  -> Dispatcher::dispatch(routeInfo)
  -> Controller action
  -> Response
```

`Dispatcher` maps controller return types automatically:

- `Response` -> sent as-is
- `array` / `JsonSerializable` -> JSON response
- `string` -> text response
- `int` -> status response
- `null` -> `204`

## Data Flow

Merlin offers flexibility in how you interact with the database. Choose the approach that fits your needs - models for object-oriented work, or Query for direct table access.

Two common entry points:

```php
// Model-centric
$user = User::find(10);
$rows = User::query()->where('status', 'active')->select();

// Table-centric
$rows = Merlin\Db\Query::new()
    ->table('users')
    ->where('status', 'active')
    ->select();
```

Write operations are terminal builder calls (`insert`, `upsert`, `update`, `delete`).

## Read/Write Separation

Merlin supports replica setups:

- Reads use `AppContext::dbRead` (fallback: `db`)
- Writes use `AppContext::dbWrite` (fallback: `db`)

Models can override defaults per class with:

- `setDefaultReadConnection()`
- `setDefaultWriteConnection()`

## Extensibility Points

- Router custom parameter validators via `addType()`
- Router route groups via `prefix()` and `middleware()`
- Dispatcher middleware groups and controller factory
- Model overrides: `source()`, `schema()`, `idFields()`, connection methods
- Query SQL escape hatches via `Sql` nodes
