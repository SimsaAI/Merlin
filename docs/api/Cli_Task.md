# 🧩 Class: Task

**Full name:** [Merlin\Cli\Task](../../src/Cli/Task.php)

Base class for all CLI task classes.

Extend this class to create a CLI task. Public methods ending in "Action"
are automatically discoverable by {@see \Console}.

## 🔐 Public Properties

- `public` [Console](Cli_Console.md) `$console` · [source](../../src/Cli/Task.php)
- `public` array `$options` · [source](../../src/Cli/Task.php)

## 🚀 Public methods

### opt() · [source](../../src/Cli/Task.php#L80)

`public function opt(string $key, mixed $default = null): mixed`

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

[Back to the Index ⤴](index.md)
