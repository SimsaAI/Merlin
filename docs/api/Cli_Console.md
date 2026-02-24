# 🧩 Class: Console

**Full name:** [Merlin\Cli\Console](../../src/Cli/Console.php)

## 🚀 Public methods

### __construct() · [source](../../src/Cli/Console.php#L35)

`public function __construct(string|null $scriptName = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$scriptName` | string\|null | `null` |  |

**➡️ Return value**

- Type: mixed


---

### addNamespace() · [source](../../src/Cli/Console.php#L41)

`public function addNamespace(string $ns): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$ns` | string | - |  |

**➡️ Return value**

- Type: void


---

### addTaskPath() · [source](../../src/Cli/Console.php#L49)

`public function addTaskPath(string $path, bool $registerAutoload = false): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$path` | string | - |  |
| `$registerAutoload` | bool | `false` |  |

**➡️ Return value**

- Type: void


---

### getDefaultAction() · [source](../../src/Cli/Console.php#L65)

`public function getDefaultAction(): string`

Get the default action method name used when no action is specified on the command line.

**➡️ Return value**

- Type: string
- Description: Default action method name (without namespace), e.g. "indexAction".


---

### setDefaultAction() · [source](../../src/Cli/Console.php#L76)

`public function setDefaultAction(string $defaultAction): void`

Set the default action method name used when no action is specified on the command line.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | string | - | Action method name, e.g. "indexAction". |

**➡️ Return value**

- Type: void

**⚠️ Throws**

- InvalidArgumentException  If the given name is empty.


---

### enableColors() · [source](../../src/Cli/Console.php#L100)

`public function enableColors(bool $colors): void`

Enable or disable ANSI color output explicitly.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$colors` | bool | - |  |

**➡️ Return value**

- Type: void


---

### hasColors() · [source](../../src/Cli/Console.php#L106)

`public function hasColors(): bool`

Check whether ANSI color output is enabled.

**➡️ Return value**

- Type: bool


---

### style() · [source](../../src/Cli/Console.php#L118)

`public function style(string $text, string ...$styles): string`

Apply one or more named ANSI styles to a string.

Style names: bold, dim, red, green, yellow, blue, magenta, cyan, white, gray,
             bred, bgreen, byellow, bcyan

When color support is disabled, the text is returned unchanged.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |
| `$styles` | string | - |  |

**➡️ Return value**

- Type: string


---

### writeln() · [source](../../src/Cli/Console.php#L131)

`public function writeln(string $text = ''): void`

Write a line to stdout (newline appended).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### line() · [source](../../src/Cli/Console.php#L137)

`public function line(string $text): void`

Plain informational line.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### success() · [source](../../src/Cli/Console.php#L143)

`public function success(string $text): void`

Success message (bright green).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### warn() · [source](../../src/Cli/Console.php#L149)

`public function warn(string $text): void`

Warning message (bright yellow).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### error() · [source](../../src/Cli/Console.php#L155)

`public function error(string $text): void`

Error message (bright red).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### muted() · [source](../../src/Cli/Console.php#L161)

`public function muted(string $text): void`

Muted / dimmed text.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### info() · [source](../../src/Cli/Console.php#L167)

`public function info(string $text): void`

Informational message (cyan).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### shouldCoerceParams() · [source](../../src/Cli/Console.php#L185)

`public function shouldCoerceParams(): bool`

Check whether automatic parameter type coercion is enabled.

When enabled, string arguments that look like integers, floats, booleans,
or NULL are converted to the corresponding PHP scalar before being passed
to the action method.

**➡️ Return value**

- Type: bool
- Description: True if parameter coercion is enabled.


---

### setCoerceParams() · [source](../../src/Cli/Console.php#L195)

`public function setCoerceParams(bool $coerceParams): void`

Enable or disable automatic parameter type coercion.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$coerceParams` | bool | - | True to enable coercion, false to pass all arguments as strings. |

**➡️ Return value**

- Type: void


---

### process() · [source](../../src/Cli/Console.php#L207)

`public function process(string|null $task = null, string|null $action = null, array $params = []): void`

Process the given task, action, and parameters.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | string\|null | `null` | The name of the task to execute. |
| `$action` | string\|null | `null` | The name of the action to execute within the task. |
| `$params` | array | `[]` | An array of parameters to pass to the action method. |

**➡️ Return value**

- Type: void


---

### autodiscover() · [source](../../src/Cli/Console.php#L299)

`public function autodiscover(): void`

Autodiscover tasks in all registered namespaces and paths

**➡️ Return value**

- Type: void


---

### helpOverview() · [source](../../src/Cli/Console.php#L471)

`public function helpOverview(): void`

Built-in help task

**➡️ Return value**

- Type: void


---

### helpTask() · [source](../../src/Cli/Console.php#L497)

`public function helpTask(string $task): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | string | - |  |

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
