# 🧩 Class: Task

**Full name:** [Merlin\Cli\Task](../../src/Cli/Task.php)

Base class for all CLI task classes.

Extend this class to create a CLI task. Public methods ending in "Action"
are automatically discoverable by [`Console`](Cli_Console.md).

## 🔐 Public Properties

- `public` [Console](Cli_Console.md) `$console` · [source](../../src/Cli/Task.php)
- `public` array `$options` · [source](../../src/Cli/Task.php)

## 🚀 Public methods

### write() · [source](../../src/Cli/Task.php#L26)

`public function write(string $text = ''): void`

Write text without a newline.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### writeln() · [source](../../src/Cli/Task.php#L32)

`public function writeln(string $text = ''): void`

Write a line of text with a newline.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### stderr() · [source](../../src/Cli/Task.php#L38)

`public function stderr(string $text = ''): void`

Write to STDERR without a newline.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### stderrln() · [source](../../src/Cli/Task.php#L44)

`public function stderrln(string $text = ''): void`

Write to STDERR with a newline.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | `''` |  |

**➡️ Return value**

- Type: void


---

### line() · [source](../../src/Cli/Task.php#L50)

`public function line(string $text): void`

Plain message with no styling. Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### info() · [source](../../src/Cli/Task.php#L56)

`public function info(string $text): void`

Informational message (cyan). Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### success() · [source](../../src/Cli/Task.php#L62)

`public function success(string $text): void`

Success message (green). Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### warn() · [source](../../src/Cli/Task.php#L68)

`public function warn(string $text): void`

Warning message (yellow). Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### error() · [source](../../src/Cli/Task.php#L74)

`public function error(string $text): void`

Error message (white on red) to STDERR. Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### muted() · [source](../../src/Cli/Task.php#L80)

`public function muted(string $text): void`

Muted / dimmed text (gray). Newline is appended.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$text` | string | - |  |

**➡️ Return value**

- Type: void


---

### option() · [source](../../src/Cli/Task.php#L106)

`public function option(string $key, mixed $default = null): mixed`

Retrieve a parsed option value by key, with an optional default.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$key` | string | - | The option name (without leading dashes). |
| `$default` | mixed | `null` | The default value to return if the option is not set. |

**➡️ Return value**

- Type: mixed
- Description: The option value or the default if not set.


---

### beforeAction() · [source](../../src/Cli/Task.php#L124)

`public function beforeAction(string $action, array $params): void`

Called before the action method is executed.

Override in a subclass to perform setup work (e.g. register event listeners based on options).
The method has access to $this->options and $this->console at this point.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$action` | string | - | The resolved PHP method name that will be invoked (e.g. "runAction"). |
| `$params` | array | - | Positional parameters that will be passed to the action. |

**➡️ Return value**

- Type: void


---

### afterAction() · [source](../../src/Cli/Task.php#L135)

`public function afterAction(string $action, array $params): void`

Called after the action method has finished executing (including when an exception is thrown).

Override in a subclass to perform teardown or post-processing work (e.g. flush collected SQL logs).

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$action` | string | - | The resolved PHP method name that was invoked (e.g. "runAction"). |
| `$params` | array | - | Positional parameters that were passed to the action. |

**➡️ Return value**

- Type: void



---

[Back to the Index ⤴](index.md)
