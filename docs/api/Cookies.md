# 🧩 Merlin\Http\Cookies

## 🔐 Properties

- `protected 📦 array $cookies`

## 🚀 Public methods

### `get()`

`public function get(string $name, mixed $default = null) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |
| `$default` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `cookie()`

`public function cookie(string $name) : Merlin\Http\Cookie`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `Merlin\Http\Cookie`

### `set()`

`public function set(string $name, mixed $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true) : Merlin\Http\Cookie`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |
| `$expires` | `🔢 int` | `0` |  |
| `$path` | `🔤 string` | `'/'` |  |
| `$domain` | `🔤 string` | `''` |  |
| `$secure` | `⚙️ bool` | `false` |  |
| `$httpOnly` | `⚙️ bool` | `true` |  |

**➡️ Return value**

- Type: `Merlin\Http\Cookie`

### `delete()`

`public function delete(string $name) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `sendAll()`

`public function sendAll() : void`

**➡️ Return value**

- Type: `void`

