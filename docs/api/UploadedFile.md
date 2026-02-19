# 🧩 Merlin\Http\UploadedFile

## 🔐 Properties

- `protected 🔤 string $name`
- `protected 🔤 string $type`
- `protected 🔤 string $tmpName`
- `protected 🔢 int $error`
- `protected 🔢 int $size`

## 🚀 Public methods

### `__construct()`

`public function __construct(string $name, string $type, string $tmpName, int $error, int $size) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` |  |
| `$type` | `🔤 string` | `` |  |
| `$tmpName` | `🔤 string` | `` |  |
| `$error` | `🔢 int` | `` |  |
| `$size` | `🔢 int` | `` |  |

**➡️ Return value**

- Type: `mixed`

### `getClientFilename()`

`public function getClientFilename() : string`

**➡️ Return value**

- Type: `string`

### `getClientMediaType()`

`public function getClientMediaType() : string`

**➡️ Return value**

- Type: `string`

### `getSize()`

`public function getSize() : int`

**➡️ Return value**

- Type: `int`

### `isValid()`

`public function isValid() : bool`

**➡️ Return value**

- Type: `bool`

### `moveTo()`

`public function moveTo(string $targetPath) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$targetPath` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

