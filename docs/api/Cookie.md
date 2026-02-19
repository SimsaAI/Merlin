# 🧩 Merlin\Http\Cookie

## 🔐 Properties

- `protected 🔤 string $name`
- `protected 🎲 mixed $value`
- `protected ⚙️ bool $loaded`
- `protected 🔢 int $expires`
- `protected 🔤 string $path`
- `protected 🔤 string $domain`
- `protected ⚙️ bool $secure`
- `protected ⚙️ bool $httpOnly`
- `protected ⚙️ bool $encrypted`
- `protected 🔤 string $cipher`
- `protected string|null $key`

## 🚀 Public methods

### `make()`

`public static function make(string $name, mixed $value = null, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true) : static`

Create a new Cookie instance with the given parameters.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | The name of the cookie. |
| `$value` | `🎲 mixed` | `null` | The value of the cookie (optional). |
| `$expires` | `🔢 int` | `0` | Expiration timestamp (optional). |
| `$path` | `🔤 string` | `'/'` | Path for which the cookie is valid (optional). |
| `$domain` | `🔤 string` | `''` | Domain for which the cookie is valid (optional). |
| `$secure` | `⚙️ bool` | `false` | Whether the cookie should only be sent over HTTPS (optional). |
| `$httpOnly` | `⚙️ bool` | `true` | Whether the cookie should be inaccessible to JavaScript (optional). |

**➡️ Return value**

- Type: `static`
- Description: A new Cookie instance.

### `__construct()`

`public function __construct(string $name, mixed $value = null, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `null` |  |
| `$expires` | `🔢 int` | `0` |  |
| `$path` | `🔤 string` | `'/'` |  |
| `$domain` | `🔤 string` | `''` |  |
| `$secure` | `⚙️ bool` | `false` |  |
| `$httpOnly` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `mixed`

### `value()`

`public function value(mixed $default = null) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$default` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `set()`

`public function set(mixed $value) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `static`

### `send()`

`public function send() : static`

**➡️ Return value**

- Type: `static`

### `delete()`

`public function delete() : void`

**➡️ Return value**

- Type: `void`

### `encrypted()`

`public function encrypted(bool $state = true) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$state` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `static`

### `cipher()`

`public function cipher(string $cipher) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$cipher` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `key()`

`public function key(string|null $key) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `string\|null` | `` |  |

**➡️ Return value**

- Type: `static`

### `name()`

`public function name() : string`

**➡️ Return value**

- Type: `string`

### `expires()`

`public function expires(int $timestamp) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$timestamp` | `🔢 int` | `` |  |

**➡️ Return value**

- Type: `static`

### `path()`

`public function path(string $path) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$path` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `domain()`

`public function domain(string $domain) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$domain` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `secure()`

`public function secure(bool $state) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$state` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `static`

### `httpOnly()`

`public function httpOnly(bool $state) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$state` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `static`

### `__toString()`

`public function __toString() : string`

**➡️ Return value**

- Type: `string`

