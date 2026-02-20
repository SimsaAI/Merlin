# 🧩 Response

**Full name:** [Merlin\Http\Response](../../src/Http/Response.php)

## 🚀 Public methods

### __construct() · [source](../../src/Http/Response.php#L6)

`public function __construct(int $status = 200, array $headers = [], string $body = ''): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$status` | int | `200` |  |
| `$headers` | array | `[]` |  |
| `$body` | string | `''` |  |

**➡️ Return value**

- Type: mixed

### setStatus() · [source](../../src/Http/Response.php#L13)

`public function setStatus(int $code): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$code` | int | - |  |

**➡️ Return value**

- Type: static

### setHeader() · [source](../../src/Http/Response.php#L19)

`public function setHeader(string $key, string $value): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | string | - |  |
| `$value` | string | - |  |

**➡️ Return value**

- Type: static

### write() · [source](../../src/Http/Response.php#L25)

`public function write(string $text): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: static

### send() · [source](../../src/Http/Response.php#L31)

`public function send(): void`

**➡️ Return value**

- Type: void

### json() · [source](../../src/Http/Response.php#L42)

`public static function json(mixed $data, int $status = 200): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$data` | mixed | - |  |
| `$status` | int | `200` |  |

**➡️ Return value**

- Type: static

### text() · [source](../../src/Http/Response.php#L51)

`public static function text(string $text, int $status = 200): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |
| `$status` | int | `200` |  |

**➡️ Return value**

- Type: static

### html() · [source](../../src/Http/Response.php#L60)

`public static function html(string $html, int $status = 200): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$html` | string | - |  |
| `$status` | int | `200` |  |

**➡️ Return value**

- Type: static

### redirect() · [source](../../src/Http/Response.php#L69)

`public static function redirect(string $url, int $status = 302): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$url` | string | - |  |
| `$status` | int | `302` |  |

**➡️ Return value**

- Type: static

### status() · [source](../../src/Http/Response.php#L78)

`public static function status(int $status): static`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$status` | int | - |  |

**➡️ Return value**

- Type: static

