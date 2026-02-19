# 🧩 Merlin\Http\Response

## 🔐 Properties

- `protected 🔢 int $status`
- `protected 📦 array $headers`
- `protected 🔤 string $body`

## 🚀 Public methods

### `__construct()`

`public function __construct(int $status = 200, array $headers = [], string $body = '') : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$status` | `🔢 int` | `200` |  |
| `$headers` | `📦 array` | `[]` |  |
| `$body` | `🔤 string` | `''` |  |

**➡️ Return value**

- Type: `mixed`

### `setStatus()`

`public function setStatus(int $code) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$code` | `🔢 int` | `` |  |

**➡️ Return value**

- Type: `static`

### `setHeader()`

`public function setHeader(string $key, string $value) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |
| `$value` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `write()`

`public function write(string $text) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `static`

### `send()`

`public function send() : void`

**➡️ Return value**

- Type: `void`

### `json()`

`public static function json(mixed $data, int $status = 200) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | `🎲 mixed` | `` |  |
| `$status` | `🔢 int` | `200` |  |

**➡️ Return value**

- Type: `static`

### `text()`

`public static function text(string $text, int $status = 200) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | `🔤 string` | `` |  |
| `$status` | `🔢 int` | `200` |  |

**➡️ Return value**

- Type: `static`

### `html()`

`public static function html(string $html, int $status = 200) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$html` | `🔤 string` | `` |  |
| `$status` | `🔢 int` | `200` |  |

**➡️ Return value**

- Type: `static`

### `redirect()`

`public static function redirect(string $url, int $status = 302) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$url` | `🔤 string` | `` |  |
| `$status` | `🔢 int` | `302` |  |

**➡️ Return value**

- Type: `static`

### `status()`

`public static function status(int $status) : static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$status` | `🔢 int` | `` |  |

**➡️ Return value**

- Type: `static`

