# API Reference

**Quick lookup for all framework classes** - Complete reference documentation for all public APIs, methods, and properties in Merlin. Use this as a quick reference when building applications.

This is a compact reference for the most-used public APIs in Merlin. For detailed usage examples and explanations, refer to the specific topic guides. Methods are listed with their signatures - return types and parameters show what each method expects and returns.

## MVC

### `Merlin\Mvc\Router`

- `__construct()`
- `add(string|array|null $method, string $pattern, string|array|null $handler = null): static`
- `match(string $uri, string $method = 'GET'): ?array`
- `setName(string $name): static`
- `hasNamedRoute(string $name): bool`
- `urlFor(string $name, array $params = [], array $query = []): string`
- `addType(string $name, callable $validator): static`
- `setParseParams(bool $parseParams): static`
- `shouldParseParams(): bool`
- `prefix(string $prefix, callable $callback): void`
- `middleware(string|array $name, callable $callback): void`

### `Merlin\Mvc\Dispatcher`

- `__construct(?Merlin\AppContext $context = null)`
- `dispatch(array $routeInfo): Merlin\Http\Response`
- `addMiddleware(MiddlewareInterface $mw): void`
- `defineMiddlewareGroup(string $name, array $middleware): void`
- `setControllerFactory(callable $factory): void`
- `setBaseNamespace(string $baseNamespace): static`
- `getBaseNamespace(): string`
- `setDefaultController(string $defaultController): static`
- `getDefaultController(): string`
- `setDefaultAction(string $defaultAction): static`
- `getDefaultAction(): string`

`dispatch()` resolves the controller/action from route info, stores the result in
`AppContext->route`, then executes the middleware pipeline and controller action.
`RoutingResult->params` is built from non-routing `vars` plus `vars['params']`, while
`vars['params']` remains the raw wildcard payload.

### `Merlin\Mvc\Controller`

- `__construct(?Merlin\AppContext $context = null)`
- `getContext(): Merlin\AppContext`
- `beforeAction(string $action, array $params): ?Merlin\Http\Response`
- `afterAction(string $action, array $params): ?Merlin\Http\Response`

`$params` in `beforeAction`/`afterAction` is the same array that is used to call
the action method (`RoutingResult->params`).

### `Merlin\RoutingResult`

- `controller: string`
- `action: string`
- `namespace: ?string`
- `vars: array`
- `params: array`
- `groups: array`
- `override: array`

### `Merlin\Mvc\ViewEngine`

- `setPath(string $path): static`
- `getPath(): string`
- `setExtension(string $ext): static`
- `setLayout(?string $layout): static`
- `addNamespace(string $name, string $path): static`
- `setVar(string $name, mixed $value): static`
- `setVars(array $vars): static`
- `render(string $view, array $vars = []): string`
- `renderPartial(string $view, array $vars = []): string`

### `Merlin\Mvc\Model`

Static:

- `query(?string $alias = null): Merlin\Db\Query`
- `create(array $values): static`
- `forceCreate(array $values): static`
- `firstOrCreate(array $conditions, array $values = []): static`
- `updateOrCreate(array $conditions, array $values = []): static`
- `find(mixed $id): ?static`
- `findOrFail(mixed $id): static`
- `findOne(array $conditions): ?static`
- `findAll(array $conditions = []): Merlin\Db\ResultSet`
- `exists(array $conditions): bool`
- `count(array $conditions = []): int`
- `setDefaultReadConnection(Merlin\Db\Database $db): void`
- `setDefaultWriteConnection(Merlin\Db\Database $db): void`

Instance:

- `source(): string`
- `schema(): ?string`
- `idFields(): array`
- `save(): bool`
- `insert(): bool`
- `update(): bool`
- `delete(): bool`
- `saveState(): static`
- `loadState(): static`
- `getState(): ?static`
- `hasChanged(): bool`
- `readConnection(): Merlin\Db\Database`
- `writeConnection(): Merlin\Db\Database`

## Database

### `Merlin\Db\Query`

- `create(?Merlin\Db\Database $db = null): static`
- `table(string $name, ?string $alias = null): static`
- `columns(string|array $columns): static`
- `where(string|Merlin\Db\Condition $condition, mixed $value = null, bool $escape = true): static`
- `orWhere(string|Merlin\Db\Condition $condition, mixed $value = null, bool $escape = true): static`
- `join(string $model, string|Merlin\Db\Condition|null $alias = null, string|Merlin\Db\Condition|null $conditions = null, ?string $type = null): static`
- `leftJoin(...)`, `rightJoin(...)`, `innerJoin(...)`, `crossJoin(...)`
- `groupBy(string|array $columns): static`
- `having(string|Merlin\Db\Condition $condition, mixed $value = null, bool $escape = true): static`
- `orderBy(array|string $orderBy): static`
- `limit(int $limit, int $offset = 0): static`
- `offset(int $offset): static`
- `distinct(bool $distinct): static`
- `forUpdate(bool $forUpdate = true): static`
- `sharedLock(bool $sharedLock = true): static`
- `set(string|array $column, mixed $value = null, bool $escape = true): static`
- `values(array|object $values, bool $escape = true): static`
- `bulkValues(array $valuesList = [], bool $escape = true): static`
- `bind(array|object $bindParams): static`
- `returning(array|string|null $columns): static`
- `conflict(array|string $target): static`
- `updateValues(array $values, bool $escape = true): static`
- `returnSql(bool $returnSql = true): static`
- `toSql(): string`
- `select(): Merlin\Db\ResultSet|string`
- `first(): Merlin\Mvc\Model|string|null`
- `insert(?array $data = null): bool|string|array|Merlin\Db\ResultSet`
- `upsert(?array $data = null): bool|string|array|Merlin\Db\ResultSet`
- `update(?array $data = null): int|string|array|Merlin\Db\ResultSet`
- `delete(): int|string|array|Merlin\Db\ResultSet`
- `exists(): bool|string`
- `count(): int|string`

### `Merlin\Db\Database`

- `__construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)`
- `query(string $query, array $bindParams = []): mixed`
- `execute(string $query, array $bindParams = []): bool`
- `begin(): bool`
- `commit(): bool`
- `rollback(): bool`
- `inTransaction(): bool`
- `getDriver(): string`
- `setLogger(Psr\Log\LoggerInterface $logger): static`

### `Merlin\Db\ResultSet`

- `firstModel(): ?Merlin\Mvc\Model`
- `nextModel(): ?Merlin\Mvc\Model`
- `allModels(): array`
- `fetchArray(): ?array`
- `fetchAssoc(): ?array`
- `fetchColumn(int $column = 0): mixed`
- `count(): int`

## HTTP

### `Merlin\Http\Request`

- Access query, post, headers, body, files, method, URI.

### `Merlin\Http\Response`

- `setStatus(int $code): static`
- `setHeader(string $key, string $value): static`
- `write(string $text): static`
- `send(): void`
- `json(mixed $data, int $status = 200): static`
- `text(string $text, int $status = 200): static`
- `html(string $html, int $status = 200): static`
- `redirect(string $url, int $status = 302): static`
- `status(int $status): static`

### `Merlin\Http\Cookies`

- Cookie read/write helpers with optional encryption.

### `Merlin\Http\Session`

- Session get/set/remove/flash helpers.

## CLI

### `Merlin\Cli\Console`

- `__construct(string $namespace = 'App\\Tasks')`
- `setNamespace(string $namespace): void`
- `setDefaultTask(string $defaultTask): void`
- `setDefaultAction(string $defaultAction): void`
- `setParseParams(bool $parseParams): void`
- `process(?string $task = null, ?string $action = null, array $params = []): mixed`

### `Merlin\Cli\Task`

- Base class for task classes and action methods.

## Related Guides

- [Getting Started](00-GETTING-STARTED.md)
- [MVC Routing](02-MVC-ROUTING.md)
- [Database Queries](05-DATABASE-QUERIES.md)
- [CLI Tasks](07-CLI-TASKS.md)
