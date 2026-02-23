# 🧩 Class: Console

**Full name:** [Merlin\Cli\Console](../../src/Cli/Console.php)

Console entry point for dispatching CLI tasks.

Resolves a task class (a subclass of {@see \Task}) and an action method based on
the command-line arguments, converts string arguments to appropriate scalar types,
and invokes the action.

## 🚀 Public methods

### getDefaultTask() · [source](../../src/Cli/Console.php#L31)

`public function getDefaultTask(): string`

Get the default task class name used when no task is specified on the command line.

**➡️ Return value**

- Type: string
- Description: Default task class name (without namespace), e.g. "MainTask".


---

### setDefaultTask() · [source](../../src/Cli/Console.php#L42)

`public function setDefaultTask(string $defaultTask): void`

Set the default task class name used when no task is specified on the command line.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultTask` | string | - | Task class name (without namespace), e.g. "MainTask". |

**➡️ Return value**

- Type: void

**⚠️ Throws**

- Exception  If the given name is empty.


---

### getDefaultAction() · [source](../../src/Cli/Console.php#L55)

`public function getDefaultAction(): string`

Get the default action method name used when no action is specified on the command line.

**➡️ Return value**

- Type: string
- Description: Default action method name (without namespace), e.g. "mainAction".


---

### setDefaultAction() · [source](../../src/Cli/Console.php#L66)

`public function setDefaultAction(string $defaultAction): void`

Set the default action method name used when no action is specified on the command line.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$defaultAction` | string | - | Action method name, e.g. "mainAction". |

**➡️ Return value**

- Type: void

**⚠️ Throws**

- Exception  If the given name is empty.


---

### getNamespace() · [source](../../src/Cli/Console.php#L79)

`public function getNamespace(): string`

Get the PHP namespace used to locate task classes.

**➡️ Return value**

- Type: string
- Description: Namespace string (always ends with a backslash), e.g. "App\\Tasks\\".


---

### setNamespace() · [source](../../src/Cli/Console.php#L92)

`public function setNamespace(string $namespace): void`

Set the PHP namespace used to locate task classes.

A trailing backslash is added automatically if missing.
Pass an empty string to disable namespace prefixing.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$namespace` | string | - | Namespace to use, e.g. "App\\Tasks". |

**➡️ Return value**

- Type: void


---

### shouldParseParams() · [source](../../src/Cli/Console.php#L112)

`public function shouldParseParams(): bool`

Check whether automatic parameter type coercion is enabled.

When enabled, string arguments that look like integers, floats, booleans,
or NULL are converted to the corresponding PHP scalar before being passed
to the action method.

**➡️ Return value**

- Type: bool
- Description: True if parameter parsing is enabled.


---

### setParseParams() · [source](../../src/Cli/Console.php#L122)

`public function setParseParams(bool $parseParams): void`

Enable or disable automatic parameter type coercion.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$parseParams` | bool | - | True to enable coercion, false to pass all arguments as strings. |

**➡️ Return value**

- Type: void


---

### process() · [source](../../src/Cli/Console.php#L142)

`public function process(string|null $task = null, string|null $action = null, array $params = []): mixed`

Resolve and invoke a task action.

Converts the task and action names to CamelCase, prepends the configured
namespace, instantiates the task class, optionally coerces the parameters,
and calls the action method.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$task` | string\|null | `null` | Task name as passed on the command line (e.g. "my-task"). Null falls back to the default task. |
| `$action` | string\|null | `null` | Action name as passed on the command line (e.g. "run"). Null falls back to the default action. |
| `$params` | array | `[]` | Remaining command-line arguments passed as positional parameters to the action. |

**➡️ Return value**

- Type: mixed
- Description: The return value of the invoked action method.

**⚠️ Throws**

- [TaskNotFoundException](Cli_Exceptions_TaskNotFoundException.md)  If the resolved task class does not exist.
- [InvalidTaskException](Cli_Exceptions_InvalidTaskException.md)  If the resolved class is not a subclass of {@see \Task}.
- ActionNotFoundException  If the resolved method does not exist on the task.



---

[Back to the Index ⤴](index.md)
