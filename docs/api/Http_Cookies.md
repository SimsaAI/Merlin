# 🧩 Class: Cookies

**Full name:** [Merlin\Http\Cookies](../../src/Http/Cookies.php)

Cookie jar that manages a collection of {@see Cookie} instances for the current request.

Acts as a central registry for reading incoming cookies and building/sending
outgoing Set-Cookie headers.

## 🚀 Public methods

### get() · [source](../../src/Http/Cookies.php#L26)

`public function get(string $name, mixed $default = null): mixed`

Read a cookie value from the incoming request.

If the cookie was set in this request via {@see \set()}, its in-memory value is
returned; otherwise the value is read from $_COOKIE.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Cookie name. |
| `$default` | mixed | `null` | Default value when the cookie is absent. |

**➡️ Return value**

- Type: mixed


---

### cookie() · [source](../../src/Http/Cookies.php#L41)

`public function cookie(string $name): Merlin\Http\Cookie`

Get (or lazily create) a {@see Cookie} instance for the given name.

Use this when you need to configure encryption, path, etc. before reading
or sending the cookie.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Cookie name. |

**➡️ Return value**

- Type: [Cookie](Http_Cookie.md)


---

### set() · [source](../../src/Http/Cookies.php#L61)

`public function set(string $name, mixed $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): Merlin\Http\Cookie`

Create and register a new {@see Cookie} with the given parameters.

The cookie is not sent until {@see \sendAll()} (or {@see \Cookie::send()}) is called.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Cookie name. |
| `$value` | mixed | - | Cookie value. |
| `$expires` | int | `0` | Expiration timestamp (0 = session cookie). |
| `$path` | string | `'/'` | URL path scope. |
| `$domain` | string | `''` | Domain scope. |
| `$secure` | bool | `false` | Send over HTTPS only. |
| `$httpOnly` | bool | `true` | Inaccessible to JavaScript. |

**➡️ Return value**

- Type: [Cookie](Http_Cookie.md)
- Description: The newly created Cookie instance for further configuration.


---

### delete() · [source](../../src/Http/Cookies.php#L80)

`public function delete(string $name): void`

Delete a cookie by emitting a Set-Cookie header with an expiration in the past.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - | Cookie name. |

**➡️ Return value**

- Type: void


---

### sendAll() · [source](../../src/Http/Cookies.php#L92)

`public function sendAll(): void`

Send all registered cookies by emitting their Set-Cookie headers.

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
