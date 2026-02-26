# 🧩 Class: Console

**Full name:** [Merlin\Cli\Console](../../src/Cli/Console.php)

## 🚀 Public methods

### __construct() · [source](../../src/Cli/Console.php#L54)

`public function __construct(string|null $scriptName = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$scriptName` | string\|null | `null` |  |

**➡️ Return value**

- Type: mixed


---

### addNamespace() · [source](../../src/Cli/Console.php#L60)

`public function addNamespace(string $ns): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$ns` | string | - |  |

**➡️ Return value**

- Type: void


---

### addTaskPath() · [source](../../src/Cli/Console.php#L68)

`public function addTaskPath(string $path, bool $registerAutoload = false): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$path` | string | - |  |
| `$registerAutoload` | bool | `false` |  |

**➡️ Return value**

- Type: void


---

### getDefaultAction() · [source](../../src/Cli/Console.php#L84)

`public function getDefaultAction(): string`

Get the default action method name used when no action is specified on the command line.

**➡️ Return value**

- Type: string
- Description: Default action method name (without namespace), e.g. "indexAction".


---

### setDefaultAction() · [source](../../src/Cli/Console.php#L95)

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

### enableColors() · [source](../../src/Cli/Console.php#L119)

`public function enableColors(bool $colors): void`

Enable or disable ANSI color output explicitly.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$colors` | bool | - |  |

**➡️ Return value**

- Type: void


---

### hasColors() · [source](../../src/Cli/Console.php#L125)

`public function hasColors(): bool`

Check whether ANSI color output is enabled.

**➡️ Return value**

- Type: bool


---

### color() · [source](../../src/Cli/Console.php#L130)

`public function color(string|int $r, int|null $g = null, int|null $b = null, mixed $background = false): string`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$r` | string\|int | - |  |
| `$g` | int\|null | `null` |  |
| `$b` | int\|null | `null` |  |
| `$background` | mixed | `false` |  |

**➡️ Return value**

- Type: string


---

### style() · [source](../../src/Cli/Console.php#L183)

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

### writeln() · [source](../../src/Cli/Console.php#L196)

`public function writeln(string $text = ''): void`

Write a line to stdout (newline appended).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### line() · [source](../../src/Cli/Console.php#L202)

`public function line(string $text): void`

Plain informational line.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### success() · [source](../../src/Cli/Console.php#L208)

`public function success(string $text): void`

Success message (bright green).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### warn() · [source](../../src/Cli/Console.php#L214)

`public function warn(string $text): void`

Warning message (bright yellow).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### error() · [source](../../src/Cli/Console.php#L220)

`public function error(string $text): void`

Error message (bright red).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### critical() · [source](../../src/Cli/Console.php#L226)

`public function critical(string $text): void`

Critical message (red on white bg).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### muted() · [source](../../src/Cli/Console.php#L232)

`public function muted(string $text): void`

Muted / dimmed text.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### info() · [source](../../src/Cli/Console.php#L238)

`public function info(string $text): void`

Informational message (cyan).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### shouldCoerceParams() · [source](../../src/Cli/Console.php#L256)

`public function shouldCoerceParams(): bool`

Check whether automatic parameter type coercion is enabled.

When enabled, string arguments that look like integers, floats, booleans,
or NULL are converted to the corresponding PHP scalar before being passed
to the action method.

**➡️ Return value**

- Type: bool
- Description: True if parameter coercion is enabled.


---

### setCoerceParams() · [source](../../src/Cli/Console.php#L266)

`public function setCoerceParams(bool $coerceParams): void`

Enable or disable automatic parameter type coercion.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$coerceParams` | bool | - | True to enable coercion, false to pass all arguments as strings. |

**➡️ Return value**

- Type: void


---

### process() · [source](../../src/Cli/Console.php#L278)

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

### autodiscover() · [source](../../src/Cli/Console.php#L372)

`public function autodiscover(): void`

Autodiscover tasks in all registered namespaces and paths

**➡️ Return value**

- Type: void


---

### helpOverview() · [source](../../src/Cli/Console.php#L544)

`public function helpOverview(): void`

Built-in help task

**➡️ Return value**

- Type: void


---

### helpTask() · [source](../../src/Cli/Console.php#L606)

`public function helpTask(string $task): void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | string | - |  |

**➡️ Return value**

- Type: void


---

### coerceParam() · [source](../../src/Cli/Console.php#L954)

`public function coerceParam(string $param): string|int|float|bool|null`

Coerce a string parameter to int, float, bool, or null if it looks like one of those.

Otherwise return the original string. Empty string is returned as-is.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$param` | string | - | The parameter string to coerce. |

**➡️ Return value**

- Type: string|int|float|bool|null
- Description: The coerced value, or original string if no coercion applied.


---

### terminalWidth() · [source](../../src/Cli/Console.php#L1040)

`public function terminalWidth(): int`

Return detected terminal width (columns). Falls back to 80.

**➡️ Return value**

- Type: int


---

### wrapText() · [source](../../src/Cli/Console.php#L1304)

`public function wrapText(string $text, int $width): array`

Word-wrap a text block into an array of lines for the given column width.

Lines are trimmed of trailing whitespace. Empty input returns an array with one empty string.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - | The text to wrap. |
| `$width` | int | - | The maximum column width for wrapping. |

**➡️ Return value**

- Type: array



---

[Back to the Index ⤴](index.md)
