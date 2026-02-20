# 🧩 SessionMiddleware

**Full name:** [Merlin\Http\SessionMiddleware](../../src/Http/SessionMiddleware.php)

Middleware to manage PHP sessions.

This middleware ensures that a session is started for each request and
provides access to session data through the AppContext. It also ensures
that session data is properly saved at the end of the request before the
response is sent.

## 🚀 Public methods

### process() · [source](../../src/Http/SessionMiddleware.php#L18)

`public function process(Merlin\AppContext $context, callable $next): Merlin\Http\Response|null`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$context` | [🧩`AppContext`](AppContext.md) | - |  |
| `$next` | `callable` | - |  |

**➡️ Return value**

- Type: [🧩`Response`](Response.md)|`null`

