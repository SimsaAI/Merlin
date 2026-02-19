# 🧩 Merlin\Http\SessionMiddleware

Middleware to manage PHP sessions.

This middleware ensures that a session is started for each request and
provides access to session data through the AppContext. It also ensures
that session data is properly saved at the end of the request before the
response is sent.

## 🚀 Public methods

### `process()`

`public function process(Merlin\AppContext $context, callable $next) : Merlin\Http\Response|null`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$context` | `Merlin\AppContext` | `` |  |
| `$next` | `callable` | `` |  |

**➡️ Return value**

- Type: `Merlin\Http\Response|null`

