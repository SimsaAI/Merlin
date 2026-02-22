# Logging

**Monitor and debug your application** - Set up comprehensive logging for database queries, application events, and errors. Learn how to use event listeners, integrate with popular logging libraries, and track performance metrics.

Merlin supports logging through event listeners on database operations and custom middleware.
Integrate your own logger or observability solution via `Database::addListener()` hooks and middleware.

## Database Listeners

Database event listeners let you monitor queries, connection issues, and transactions. This is perfect for debugging, performance monitoring, or audit trails.

`Merlin\Db\Database` supports event listeners:

```php
use Merlin\Db\Database;

$db = new Database($dsn, $user, $pass);

$db->addListener(function (string $event, ...$args) {
    error_log('[db] ' . $event . ' ' . json_encode($args));
});
```

Common events include:

- `db.beforeQuery`, `db.afterQuery`
- `db.beforePrepare`, `db.afterPrepare`
- `db.beforeExecute`, `db.afterExecute`
- `db.beforeBegin`, `db.afterBegin`
- `db.beforeCommit`, `db.afterCommit`
- `db.beforeRollback`, `db.afterRollback`
- `db.exception`
- `db.reconnectAttempt`, `db.reconnected`, `db.reconnectFailed`, `db.reconnectAborted`

## Exception Handling and Logging

Capturing and logging exceptions is critical for debugging production issues. Middleware is the ideal place to implement global exception handling.

Implement a middleware to catch and log exceptions during request processing:

```php
use Merlin\Mvc\MiddlewareInterface;
use Merlin\AppContext;
use Merlin\Http\Response;

class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private bool $debug = false) {}

    public function process(AppContext $context, callable $next): ?Response
    {
        try {
            return $next($context);
        } catch (\Throwable $e) {

            // Default: PHP-like logging
            error_log($e);

            if ($this->debug) {
                return Response::json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ], 500);
            }

            return Response::json(['error' => 'Internal Server Error'], 500);
        }
    }
}
```

Register this middleware first in your middleware stack to ensure all exceptions are caught and logged.

## Integrating Your Own Logger

You can forward database events to any logger implementation:

```php
$db->addListener(function (string $event, ...$args) use ($logger) {
    $logger->info('db_event', [
        'event' => $event,
        'args' => $args,
    ]);
});
```

Use Monolog, Sentry, or any custom abstraction from your app.

## Practical Recommendations

- Log query lifecycle events in development for debugging.
- In production, log only higher-signal events (`db.exception`, reconnect events) to limit noise.
- Redact secrets and PII from logged parameters.
- Add correlation/request IDs at the application level.

## Related

- [Database Queries](05-DATABASE-QUERIES.md)
- [Security](08-SECURITY.md)
- [src/Db/Database.php](../src/Db/Database.php)
