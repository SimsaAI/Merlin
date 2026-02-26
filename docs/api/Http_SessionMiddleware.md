# 🧩 Class: SessionMiddleware

**Full name:** [Merlin\Http\SessionMiddleware](../../src/Http/SessionMiddleware.php)

Middleware to manage PHP sessions.

This middleware ensures that a session is started for each request and
provides access to session data through the AppContext. It also ensures
that session data is properly saved at the end of the request before the
response is sent.

## 🚀 Public methods

### process() · [source](../../src/Http/SessionMiddleware.php#L26)

`public function process(Merlin\AppContext $context, callable $next): Merlin\Http\Response|null`

Start the PHP session, expose it through [`AppContext::session()`](AppContext.md#session),
invoke the next middleware, then flush the session to storage.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$context` | [AppContext](AppContext.md) | - | Application context. |
| `$next` | callable | - | Next middleware callable. |

**➡️ Return value**

- Type: [Response](Http_Response.md)|null
- Description: The response from the downstream pipeline.



---

[Back to the Index ⤴](index.md)
