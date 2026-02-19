# 🧩 Merlin\Http\Session

## 🔐 Properties

- `private 📦 array $store`

## 🚀 Public methods

### `__construct()`

`public function __construct(array &$store) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$store` | `📦 array` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `get()`

`public function get(string $key, mixed $default = null) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |
| `$default` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `set()`

`public function set(string $key, mixed $value) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |
| `$value` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `void`

### `remove()`

`public function remove(string $key) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `has()`

`public function has(string $key) : bool`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `bool`

### `clear()`

`public function clear() : void`

**➡️ Return value**

- Type: `void`

