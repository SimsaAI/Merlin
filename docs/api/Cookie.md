# 🧩 Cookie

**Full name:** [Merlin\Http\Cookie](../../src/Http/Cookie.php)

## 🔐 Properties

- `protected` 🔤 `string` `$name` · [source](../../src/Http/Cookie.php)
- `protected` 🎲 `mixed` `$value` · [source](../../src/Http/Cookie.php)
- `protected` ⚙️ `bool` `$loaded` · [source](../../src/Http/Cookie.php)
- `protected` 🔢 `int` `$expires` · [source](../../src/Http/Cookie.php)
- `protected` 🔤 `string` `$path` · [source](../../src/Http/Cookie.php)
- `protected` 🔤 `string` `$domain` · [source](../../src/Http/Cookie.php)
- `protected` ⚙️ `bool` `$secure` · [source](../../src/Http/Cookie.php)
- `protected` ⚙️ `bool` `$httpOnly` · [source](../../src/Http/Cookie.php)
- `protected` ⚙️ `bool` `$encrypted` · [source](../../src/Http/Cookie.php)
- `protected` 🔤 `string` `$cipher` · [source](../../src/Http/Cookie.php)
- `protected` 🔤 `string`|`null` `$key` · [source](../../src/Http/Cookie.php)

## 🚀 Public methods

### make() · [source](../../src/Http/Cookie.php#L38)

`public static function make(string $name, mixed $value = null, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): static`

Create a new Cookie instance with the given parameters.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🔤 `string` | - | The name of the cookie. |
| `$value` | 🎲 `mixed` | `null` | The value of the cookie (optional). |
| `$expires` | 🔢 `int` | `0` | Expiration timestamp (optional). |
| `$path` | 🔤 `string` | `'/'` | Path for which the cookie is valid (optional). |
| `$domain` | 🔤 `string` | `''` | Domain for which the cookie is valid (optional). |
| `$secure` | ⚙️ `bool` | `false` | Whether the cookie should only be sent over HTTPS (optional). |
| `$httpOnly` | ⚙️ `bool` | `true` | Whether the cookie should be inaccessible to JavaScript (optional). |

**➡️ Return value**

- Type: 🧩 `static`
- Description: A new Cookie instance.

### __construct() · [source](../../src/Http/Cookie.php#L52)

`public function __construct(string $name, mixed $value = null, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): mixed`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🔤 `string` | - |  |
| `$value` | 🎲 `mixed` | `null` |  |
| `$expires` | 🔢 `int` | `0` |  |
| `$path` | 🔤 `string` | `'/'` |  |
| `$domain` | 🔤 `string` | `''` |  |
| `$secure` | ⚙️ `bool` | `false` |  |
| `$httpOnly` | ⚙️ `bool` | `true` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### value() · [source](../../src/Http/Cookie.php#L77)

`public function value(mixed $default = null): mixed`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$default` | 🎲 `mixed` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### set() · [source](../../src/Http/Cookie.php#L99)

`public function set(mixed $value): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$value` | 🎲 `mixed` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### send() · [source](../../src/Http/Cookie.php#L108)

`public function send(): static`

**➡️ Return value**

- Type: 🧩 `static`

### delete() · [source](../../src/Http/Cookie.php#L129)

`public function delete(): void`

**➡️ Return value**

- Type: `void`

### encrypted() · [source](../../src/Http/Cookie.php#L144)

`public function encrypted(bool $state = true): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$state` | ⚙️ `bool` | `true` |  |

**➡️ Return value**

- Type: 🧩 `static`

### cipher() · [source](../../src/Http/Cookie.php#L150)

`public function cipher(string $cipher): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$cipher` | 🔤 `string` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### key() · [source](../../src/Http/Cookie.php#L156)

`public function key(string|null $key): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$key` | 🔤 `string`\|`null` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### name() · [source](../../src/Http/Cookie.php#L183)

`public function name(): string`

**➡️ Return value**

- Type: 🔤 `string`

### expires() · [source](../../src/Http/Cookie.php#L188)

`public function expires(int $timestamp): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$timestamp` | 🔢 `int` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### path() · [source](../../src/Http/Cookie.php#L194)

`public function path(string $path): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$path` | 🔤 `string` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### domain() · [source](../../src/Http/Cookie.php#L200)

`public function domain(string $domain): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$domain` | 🔤 `string` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### secure() · [source](../../src/Http/Cookie.php#L206)

`public function secure(bool $state): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$state` | ⚙️ `bool` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### httpOnly() · [source](../../src/Http/Cookie.php#L212)

`public function httpOnly(bool $state): static`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$state` | ⚙️ `bool` | - |  |

**➡️ Return value**

- Type: 🧩 `static`

### __toString() · [source](../../src/Http/Cookie.php#L218)

`public function __toString(): string`

**➡️ Return value**

- Type: 🔤 `string`

