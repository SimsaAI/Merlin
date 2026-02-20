# 🧩 Cookies

**Full name:** [Merlin\Http\Cookies](../../src/Http/Cookies.php)

## 🚀 Public methods

### get() · [source](../../src/Http/Cookies.php#L10)

`public function get(string $name, mixed $default = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |
| `$default` | mixed | `null` |  |

**➡️ Return value**

- Type: mixed

### cookie() · [source](../../src/Http/Cookies.php#L16)

`public function cookie(string $name): Merlin\Http\Cookie`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: [Cookie](Cookie.md)

### set() · [source](../../src/Http/Cookies.php#L22)

`public function set(string $name, mixed $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): Merlin\Http\Cookie`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |
| `$value` | mixed | - |  |
| `$expires` | int | `0` |  |
| `$path` | string | `'/'` |  |
| `$domain` | string | `''` |  |
| `$secure` | bool | `false` |  |
| `$httpOnly` | bool | `true` |  |

**➡️ Return value**

- Type: [Cookie](Cookie.md)

### delete() · [source](../../src/Http/Cookies.php#L36)

`public function delete(string $name): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | string | - |  |

**➡️ Return value**

- Type: void

### sendAll() · [source](../../src/Http/Cookies.php#L45)

`public function sendAll(): void`

**➡️ Return value**

- Type: void

