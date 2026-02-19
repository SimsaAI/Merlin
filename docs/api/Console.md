# 🧩 Merlin\Cli\Console

## 🔐 Properties

- `protected 🔤 string $defaultTask`
- `protected 🔤 string $defaultAction`
- `protected 🔤 string $namespace`
- `protected ⚙️ bool $parseParams`

## 🚀 Public methods

### `getDefaultTask()`

`public function getDefaultTask() : string`

**➡️ Return value**

- Type: `string`

### `setDefaultTask()`

`public function setDefaultTask(string $defaultTask) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultTask` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `getDefaultAction()`

`public function getDefaultAction() : string`

**➡️ Return value**

- Type: `string`

### `setDefaultAction()`

`public function setDefaultAction(string $defaultAction) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `getNamespace()`

`public function getNamespace() : string`

**➡️ Return value**

- Type: `string`

### `setNamespace()`

`public function setNamespace(string $namespace) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$namespace` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `shouldParseParams()`

`public function shouldParseParams() : bool`

**➡️ Return value**

- Type: `bool`

### `setParseParams()`

`public function setParseParams(bool $parseParams) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$parseParams` | `⚙️ bool` | `` |  |

**➡️ Return value**

- Type: `void`

### `process()`

`public function process(string|null $task = null, string|null $action = null, array $params = []) : mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | `string\|null` | `null` |  |
| `$action` | `string\|null` | `null` |  |
| `$params` | `📦 array` | `[]` |  |

**➡️ Return value**

- Type: `mixed`

**⚠️ Throws**

- \TaskNotFoundException 
- \ActionNotFoundException 

