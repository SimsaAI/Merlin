# 🧩 UploadedFile

**Full name:** [Merlin\Http\UploadedFile](../../src/Http/UploadedFile.php)

## 🔐 Properties

- `protected` string `$name` · [source](../../src/Http/UploadedFile.php)
- `protected` string `$type` · [source](../../src/Http/UploadedFile.php)
- `protected` string `$tmpName` · [source](../../src/Http/UploadedFile.php)
- `protected` int `$error` · [source](../../src/Http/UploadedFile.php)
- `protected` int `$size` · [source](../../src/Http/UploadedFile.php)

## 🚀 Public methods

### __construct() · [source](../../src/Http/UploadedFile.php#L7)

`public function __construct(string $name, string $type, string $tmpName, int $error, int $size): mixed`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$name` | string | - |  |
| `$type` | string | - |  |
| `$tmpName` | string | - |  |
| `$error` | int | - |  |
| `$size` | int | - |  |

**➡️ Return value**

- Type: mixed

### getClientFilename() · [source](../../src/Http/UploadedFile.php#L16)

`public function getClientFilename(): string`

**➡️ Return value**

- Type: string

### getClientMediaType() · [source](../../src/Http/UploadedFile.php#L21)

`public function getClientMediaType(): string`

**➡️ Return value**

- Type: string

### getSize() · [source](../../src/Http/UploadedFile.php#L26)

`public function getSize(): int`

**➡️ Return value**

- Type: int

### isValid() · [source](../../src/Http/UploadedFile.php#L31)

`public function isValid(): bool`

**➡️ Return value**

- Type: bool

### moveTo() · [source](../../src/Http/UploadedFile.php#L36)

`public function moveTo(string $targetPath): void`

**🧭 Parameters**

| 🔑 Name | 🧩 Type | 🏷️ Default | 📝 Description |
|---|---|---|---|
| `$targetPath` | string | - |  |

**➡️ Return value**

- Type: void

