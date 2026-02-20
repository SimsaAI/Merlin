# 🧩 Request

**Full name:** [Merlin\Http\Request](../../src/Http/Request.php)

HTTP Request class

## 🚀 Public methods

### getRequestBody() · [source](../../src/Http/Request.php#L15)

`public function getRequestBody(): mixed`

Get the raw request body
Caches the body since php://input can only be read once

**➡️ Return value**

- Type: 🎲 `mixed`

### getJsonBody() · [source](../../src/Http/Request.php#L29)

`public function getJsonBody(mixed $assoc = true): mixed`

Get and parse JSON request body

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$assoc` | 🎲 `mixed` | `true` | When true, returns associative arrays. When false, returns objects |

**➡️ Return value**

- Type: 🎲 `mixed`
- Description: Returns the parsed JSON data, or null on error

### get() · [source](../../src/Http/Request.php#L45)

`public function get(mixed $name = null, mixed $defaultValue = null): mixed`

Get a parameter from the request (GET, POST, COOKIE, etc.)

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | `null` |  |
| `$defaultValue` | 🎲 `mixed` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getPost() · [source](../../src/Http/Request.php#L56)

`public function getPost(mixed $name = null, mixed $defaultValue = null): mixed`

Get a POST parameter from the request

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | `null` |  |
| `$defaultValue` | 🎲 `mixed` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getQuery() · [source](../../src/Http/Request.php#L67)

`public function getQuery(mixed $name = null, mixed $defaultValue = null): mixed`

Get a query parameter from the request

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | `null` |  |
| `$defaultValue` | 🎲 `mixed` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getServer() · [source](../../src/Http/Request.php#L78)

`public function getServer(mixed $name = null, mixed $defaultValue = null): mixed`

Get a server variable from the request

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | `null` |  |
| `$defaultValue` | 🎲 `mixed` | `null` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getMethod() · [source](../../src/Http/Request.php#L87)

`public function getMethod(): mixed`

Get the HTTP method of the request, accounting for method overrides in POST requests

**➡️ Return value**

- Type: 🎲 `mixed`

### getScheme() · [source](../../src/Http/Request.php#L107)

`public function getScheme(): mixed`

Get the request scheme (http or https)

**➡️ Return value**

- Type: 🎲 `mixed`

### getServerName() · [source](../../src/Http/Request.php#L116)

`public function getServerName(): mixed`

Get the server name from the request

**➡️ Return value**

- Type: 🎲 `mixed`

### getServerAddr() · [source](../../src/Http/Request.php#L125)

`public function getServerAddr(): mixed`

Get the server IP address

**➡️ Return value**

- Type: 🎲 `mixed`

### getHttpHost() · [source](../../src/Http/Request.php#L134)

`public function getHttpHost(): mixed`

Get the host from the request, accounting for Host header and server variables

**➡️ Return value**

- Type: 🎲 `mixed`

### getPort() · [source](../../src/Http/Request.php#L152)

`public function getPort(): mixed`

Get the port number from the request, accounting for standard ports and Host header

**➡️ Return value**

- Type: 🎲 `mixed`

### getContentType() · [source](../../src/Http/Request.php#L169)

`public function getContentType(): mixed`

Get the Content-Type header from the request

**➡️ Return value**

- Type: 🎲 `mixed`

### getClientAddress() · [source](../../src/Http/Request.php#L182)

`public function getClientAddress(mixed $trustForwardedHeader = false): mixed`

Get the client's IP address, optionally trusting proxy headers

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$trustForwardedHeader` | 🎲 `mixed` | `false` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getUri() · [source](../../src/Http/Request.php#L214)

`public function getUri(): mixed`

Get the request URI

**➡️ Return value**

- Type: 🎲 `mixed`

### getPath() · [source](../../src/Http/Request.php#L223)

`public function getPath(): string`

Get the request path (URI without query string)

**➡️ Return value**

- Type: 🔤 `string`

### getUserAgent() · [source](../../src/Http/Request.php#L233)

`public function getUserAgent(): mixed`

Get the User-Agent header from the request

**➡️ Return value**

- Type: 🎲 `mixed`

### getAcceptableContent() · [source](../../src/Http/Request.php#L284)

`public function getAcceptableContent(mixed $sort = false): mixed`

Gets an array with mime/types and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT"]

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$sort` | 🎲 `mixed` | `false` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getBestAccept() · [source](../../src/Http/Request.php#L293)

`public function getBestAccept(): mixed`

Gets best mime/type accepted by the browser/client from _SERVER["HTTP_ACCEPT"]

**➡️ Return value**

- Type: 🎲 `mixed`

### getClientCharsets() · [source](../../src/Http/Request.php#L302)

`public function getClientCharsets(mixed $sort = false): mixed`

Gets a charsets array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$sort` | 🎲 `mixed` | `false` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getBestCharset() · [source](../../src/Http/Request.php#L311)

`public function getBestCharset(): mixed`

Gets best charset accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]

**➡️ Return value**

- Type: 🎲 `mixed`

### getLanguages() · [source](../../src/Http/Request.php#L319)

`public function getLanguages(mixed $sort = false): mixed`

Gets languages array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$sort` | 🎲 `mixed` | `false` |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getBestLanguage() · [source](../../src/Http/Request.php#L327)

`public function getBestLanguage(): mixed`

Gets best language accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]

**➡️ Return value**

- Type: 🎲 `mixed`

### getBasicAuth() · [source](../../src/Http/Request.php#L336)

`public function getBasicAuth(): mixed`

Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']

**➡️ Return value**

- Type: 🎲 `mixed`

### getDigestAuth() · [source](../../src/Http/Request.php#L351)

`public function getDigestAuth(): mixed`

Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']

**➡️ Return value**

- Type: 🎲 `mixed`

### isAjax() · [source](../../src/Http/Request.php#L369)

`public function isAjax(): bool`

Checks whether request has been made using AJAX

**➡️ Return value**

- Type: ⚙️ `bool`

### isSoap() · [source](../../src/Http/Request.php#L401)

`public function isSoap(): mixed`

Checks whether request has been made using SOAP

**➡️ Return value**

- Type: 🎲 `mixed`

### isSecure() · [source](../../src/Http/Request.php#L414)

`public function isSecure(): mixed`

Checks whether request has been made using HTTPS

**➡️ Return value**

- Type: 🎲 `mixed`

### isPost() · [source](../../src/Http/Request.php#L423)

`public function isPost(): mixed`

Checks whether request has been made using GET method

**➡️ Return value**

- Type: 🎲 `mixed`

### has() · [source](../../src/Http/Request.php#L433)

`public function has(mixed $name): mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | - |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### hasPost() · [source](../../src/Http/Request.php#L443)

`public function hasPost(mixed $name): mixed`

Checks whether request has been made using POST method

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | - |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### hasQuery() · [source](../../src/Http/Request.php#L453)

`public function hasQuery(mixed $name): mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | - |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### hasServer() · [source](../../src/Http/Request.php#L463)

`public function hasServer(mixed $name): mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | 🎲 `mixed` | - |  |

**➡️ Return value**

- Type: 🎲 `mixed`

### getFile() · [source](../../src/Http/Request.php#L508)

`public function getFile(string $key): Merlin\Http\UploadedFile|null`

Get an uploaded file for a given key. Returns an UploadedFile object or null if no file was uploaded for the key.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$key` | 🔤 `string` | - |  |

**➡️ Return value**

- Type: [🧩`UploadedFile`](UploadedFile.md)|`null`

### getFiles() · [source](../../src/Http/Request.php#L526)

`public function getFiles(string $key): array`

Get uploaded files for a given key. Returns an array of UploadedFile objects, even if only one file was uploaded.

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$key` | 🔤 `string` | - |  |

**➡️ Return value**

- Type: 📦 `array`

