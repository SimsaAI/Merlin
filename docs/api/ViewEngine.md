# 🧩 Merlin\Mvc\ViewEngine

## 🔐 Properties

- `protected 🔤 string $extension`
- `protected 📦 array $namespaces`
- `protected 🔤 string $path`
- `protected 🔢 int $renderDepth`
- `protected string|null $layout`
- `protected 📦 array $vars`

## 🚀 Public methods

### `__construct()`

`public function __construct(array $vars = []) : mixed`

Create a new ViewEngine instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$vars` | `📦 array` | `[]` | Initial variables available to all views. |

**➡️ Return value**

- Type: `mixed`

### `setExtension()`

`public function setExtension(string $ext) : static`

Set the view file extension for this instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$ext` | `🔤 string` | `` | Extension with or without a leading dot. |

**➡️ Return value**

- Type: `static`

### `getExtension()`

`public function getExtension() : string`

Get the effective file extension used when resolving templates.

**➡️ Return value**

- Type: `string`
- Description: Extension including leading dot or empty string.

### `addNamespace()`

`public function addNamespace(string $name, string $path) : static`

Add a namespace for view resolution.

Views can be referenced using the syntax "namespace::view.name".

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | Namespace name to register. |
| `$path` | `🔤 string` | `` | Filesystem path corresponding to the namespace. |

**➡️ Return value**

- Type: `static`

### `getNamespaces()`

`public function getNamespaces() : array`

Get the currently registered view namespaces.

**➡️ Return value**

- Type: `array`
- Description: Associative array of namespace => path mappings.

### `setPath()`

`public function setPath(string $path) : static`

Set the base path for resolving relative view names.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$path` | `🔤 string` | `` | Base directory for views. |

**➡️ Return value**

- Type: `static`

### `getPath()`

`public function getPath() : string`

Get the currently configured base path for view resolution.

**➡️ Return value**

- Type: `string`
- Description: Base directory for views.

### `setLayout()`

`public function setLayout(string|null $layout) : static`

Set the layout template name to be used when calling `render()`.

The layout will receive a `content` variable containing the
rendered view output.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$layout` | `string\|null` | `` | Layout view name or null to disable. |

**➡️ Return value**

- Type: `static`

### `getLayout()`

`public function getLayout() : string|null`

Get the currently configured layout view name.

**➡️ Return value**

- Type: `string|null`
- Description: Layout name or null when none set.

### `setVar()`

`public function setVar(string $name, mixed $value) : static`

Set a single view variable.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$name` | `🔤 string` | `` | Variable name available inside templates. |
| `$value` | `🎲 mixed` | `` | Value assigned to the variable. |

**➡️ Return value**

- Type: `static`

### `setVars()`

`public function setVars(array $vars) : static`

Merge multiple variables into the view's variable set.

Later values override earlier ones for the same keys.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$vars` | `📦 array` | `` | Associative array of variables. |

**➡️ Return value**

- Type: `static`

### `render()`

`public function render(string $view, array $vars = []) : string`

Render a view (and optional layout) and echo the result.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$view` | `🔤 string` | `` | View name to render. |
| `$vars` | `📦 array` | `[]` | Additional variables for this render call. |

**➡️ Return value**

- Type: `string`
- Description: Rendered content.

### `renderPartial()`

`public function renderPartial(string $view, array $vars = []) : string`

Render a partial view template and return the generated output.

This method extracts variables into the local scope of the template
and captures the output buffer.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$view` | `🔤 string` | `` | View name to resolve and render. |
| `$vars` | `📦 array` | `[]` | Variables for this render call. |

**➡️ Return value**

- Type: `string`
- Description: Rendered HTML/output.

**⚠️ Throws**

- \Exception If the view file cannot be resolved.

### `renderLayout()`

`public function renderLayout(string $layout, string $content, array $vars = []) : string`

Render a layout template wrapping provided content.

The layout receives the content in the `content` variable.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$layout` | `🔤 string` | `` | Layout view name. |
| `$content` | `🔤 string` | `` | Previously rendered content. |
| `$vars` | `📦 array` | `[]` | Additional variables to pass to the layout. |

**➡️ Return value**

- Type: `string`
- Description: Rendered layout output.

### `getRenderDepth()`

`public function getRenderDepth() : int`

Get current render nesting depth. Useful to detect top-level renders
(depth 0) when deciding whether to apply a layout.

**➡️ Return value**

- Type: `int`
- Description: Current render depth (0 = top-level).

