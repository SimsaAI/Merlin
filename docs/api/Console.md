# 🧩 Console

**Full name:** [Merlin\Cli\Console](../../src/Cli/Console.php)

## 🚀 Public methods

### getDefaultTask() · [source](../../src/Cli/Console.php#L19)

`public function getDefaultTask(): string`

**➡️ Return value**

- Type: string

### setDefaultTask() · [source](../../src/Cli/Console.php#L24)

`public function setDefaultTask(string $defaultTask): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultTask` | string | - |  |

**➡️ Return value**

- Type: void

### getDefaultAction() · [source](../../src/Cli/Console.php#L32)

`public function getDefaultAction(): string`

**➡️ Return value**

- Type: string

### setDefaultAction() · [source](../../src/Cli/Console.php#L37)

`public function setDefaultAction(string $defaultAction): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | string | - |  |

**➡️ Return value**

- Type: void

### getNamespace() · [source](../../src/Cli/Console.php#L45)

`public function getNamespace(): string`

**➡️ Return value**

- Type: string

### setNamespace() · [source](../../src/Cli/Console.php#L50)

`public function setNamespace(string $namespace): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$namespace` | string | - |  |

**➡️ Return value**

- Type: void

### shouldParseParams() · [source](../../src/Cli/Console.php#L61)

`public function shouldParseParams(): bool`

**➡️ Return value**

- Type: bool

### setParseParams() · [source](../../src/Cli/Console.php#L66)

`public function setParseParams(bool $parseParams): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$parseParams` | bool | - |  |

**➡️ Return value**

- Type: void

### process() · [source](../../src/Cli/Console.php#L79)

`public function process(string|null $task = null, string|null $action = null, array $params = []): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | string\|null | `null` |  |
| `$action` | string\|null | `null` |  |
| `$params` | array | `[]` |  |

**➡️ Return value**

- Type: mixed

**⚠️ Throws**

- [TaskNotFoundException](TaskNotFoundException.md)
- [ActionNotFoundException](ActionNotFoundException.md)

