# 🧩 Merlin\Http\Request

HTTP Request class

## 🚀 Public methods

### `getRequestBody()`

`public function getRequestBody() : mixed`

Get the raw request body
Caches the body since php://input can only be read once

**➡️ Return value**

- Type: `mixed`

### `getJsonBody()`

`public function getJsonBody($assoc = true) : mixed`

Get and parse JSON request body

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$assoc` | `🎲 mixed` | `true` | When true, returns associative arrays. When false, returns objects |

**➡️ Return value**

- Type: `mixed`
- Description: Returns the parsed JSON data, or null on error

### `get()`

`public function get($name = null, $defaultValue = null) : mixed`

Get a parameter from the request (GET, POST, COOKIE, etc.)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `null` |  |
| `$defaultValue` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `getPost()`

`public function getPost($name = null, $defaultValue = null) : mixed`

Get a POST parameter from the request

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `null` |  |
| `$defaultValue` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `getQuery()`

`public function getQuery($name = null, $defaultValue = null) : mixed`

Get a query parameter from the request

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `null` |  |
| `$defaultValue` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `getServer()`

`public function getServer($name = null, $defaultValue = null) : mixed`

Get a server variable from the request

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `null` |  |
| `$defaultValue` | `🎲 mixed` | `null` |  |

**➡️ Return value**

- Type: `mixed`

### `getMethod()`

`public function getMethod() : mixed`

Get the HTTP method of the request, accounting for method overrides in POST requests

**➡️ Return value**

- Type: `mixed`

### `getScheme()`

`public function getScheme() : mixed`

Get the request scheme (http or https)

**➡️ Return value**

- Type: `mixed`

### `getServerName()`

`public function getServerName() : mixed`

Get the server name from the request

**➡️ Return value**

- Type: `mixed`

### `getServerAddr()`

`public function getServerAddr() : mixed`

Get the server IP address

**➡️ Return value**

- Type: `mixed`

### `getHttpHost()`

`public function getHttpHost() : mixed`

Get the host from the request, accounting for Host header and server variables

**➡️ Return value**

- Type: `mixed`

### `getPort()`

`public function getPort() : mixed`

Get the port number from the request, accounting for standard ports and Host header

**➡️ Return value**

- Type: `mixed`

### `getContentType()`

`public function getContentType() : mixed`

Get the Content-Type header from the request

**➡️ Return value**

- Type: `mixed`

### `getClientAddress()`

`public function getClientAddress($trustForwardedHeader = false) : mixed`

Get the client's IP address, optionally trusting proxy headers

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$trustForwardedHeader` | `🎲 mixed` | `false` |  |

**➡️ Return value**

- Type: `mixed`

### `getUri()`

`public function getUri() : mixed`

Get the request URI

**➡️ Return value**

- Type: `mixed`

### `getPath()`

`public function getPath() : string`

Get the request path (URI without query string)

**➡️ Return value**

- Type: `string`

### `getUserAgent()`

`public function getUserAgent() : mixed`

Get the User-Agent header from the request

**➡️ Return value**

- Type: `mixed`

### `getAcceptableContent()`

`public function getAcceptableContent($sort = false) : mixed`

Gets an array with mime/types and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT"]

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$sort` | `🎲 mixed` | `false` |  |

**➡️ Return value**

- Type: `mixed`

### `getBestAccept()`

`public function getBestAccept() : mixed`

Gets best mime/type accepted by the browser/client from _SERVER["HTTP_ACCEPT"]

**➡️ Return value**

- Type: `mixed`

### `getClientCharsets()`

`public function getClientCharsets($sort = false) : mixed`

Gets a charsets array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$sort` | `🎲 mixed` | `false` |  |

**➡️ Return value**

- Type: `mixed`

### `getBestCharset()`

`public function getBestCharset() : mixed`

Gets best charset accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]

**➡️ Return value**

- Type: `mixed`

### `getLanguages()`

`public function getLanguages($sort = false) : mixed`

Gets languages array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$sort` | `🎲 mixed` | `false` |  |

**➡️ Return value**

- Type: `mixed`

### `getBestLanguage()`

`public function getBestLanguage() : mixed`

Gets best language accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]

**➡️ Return value**

- Type: `mixed`

### `getBasicAuth()`

`public function getBasicAuth() : mixed`

Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']

**➡️ Return value**

- Type: `mixed`

### `getDigestAuth()`

`public function getDigestAuth() : mixed`

Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']

**➡️ Return value**

- Type: `mixed`

### `isAjax()`

`public function isAjax() : bool`

Checks whether request has been made using AJAX

**➡️ Return value**

- Type: `bool`

### `isSoap()`

`public function isSoap() : mixed`

Checks whether request has been made using SOAP

**➡️ Return value**

- Type: `mixed`

### `isSecure()`

`public function isSecure() : mixed`

Checks whether request has been made using HTTPS

**➡️ Return value**

- Type: `mixed`

### `isPost()`

`public function isPost() : mixed`

Checks whether request has been made using GET method

**➡️ Return value**

- Type: `mixed`

### `has()`

`public function has($name) : mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `hasPost()`

`public function hasPost($name) : mixed`

Checks whether request has been made using POST method

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `hasQuery()`

`public function hasQuery($name) : mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `hasServer()`

`public function hasServer($name) : mixed`

Checks whether request has been made using GET method

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🎲 mixed` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `getFile()`

`public function getFile(string $key) : Merlin\Http\UploadedFile|null`

Get an uploaded file for a given key. Returns an UploadedFile object or null if no file was uploaded for the key.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `Merlin\Http\UploadedFile|null`

### `getFiles()`

`public function getFiles(string $key) : array`

Get uploaded files for a given key. Returns an array of UploadedFile objects, even if only one file was uploaded.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `array`

