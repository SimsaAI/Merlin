<?php
namespace Merlin\Mvc;

class ViewEngine
{
    protected string $extension = '.php';
    protected array $namespaces = [];
    protected string $path = __DIR__ . '/../../../../../views';
    protected int $renderDepth = 0;
    protected ?string $layout = null;
    protected array $vars = [];

    /**
     * Create a new ViewEngine instance.
     *
     * @param array $vars Initial variables available to all views.
     */
    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }

    /**
     * Set the view file extension for this instance.
     *
     * @param string $ext Extension with or without a leading dot.
     * @return $this
     */
    public function setExtension(string $ext): static
    {
        if ($ext !== '' && !str_starts_with($ext, '.')) {
            $ext = '.' . $ext;
        }
        $this->extension = $ext;
        return $this;
    }

    /**
     * Get the effective file extension used when resolving templates.
     *
     * @return string Extension including leading dot or empty string.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Add a namespace for view resolution.
     *
     * Views can be referenced using the syntax "namespace::view.name".
     *
     * @param string $name Namespace name to register.
     * @param string $path Filesystem path corresponding to the namespace.
     * @return $this
     */
    public function addNamespace(string $name, string $path): static
    {
        $this->namespaces[$name] = rtrim($path, '/');
        return $this;
    }

    /**
     * Get the currently registered view namespaces.
     *
     * @return array Associative array of namespace => path mappings.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }


    /**
     * Set the base path for resolving relative view names.
     *
     * @param string $path Base directory for views.
     * @return $this
     */
    public function setPath(string $path): static
    {
        $this->path = rtrim($path, '/');
        return $this;
    }

    /**
     * Get the currently configured base path for view resolution.
     *
     * @return string Base directory for views.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the layout template name to be used when calling `render()`.
     *
     * The layout will receive a `content` variable containing the
     * rendered view output.
     *
     * @param string|null $layout Layout view name or null to disable.
     * @return $this
     */
    public function setLayout(?string $layout): static
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Get the currently configured layout view name.
     *
     * @return string|null Layout name or null when none set.
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * Set a single view variable.
     *
     * @param string $name Variable name available inside templates.
     * @param mixed $value Value assigned to the variable.
     * @return $this
     */
    public function setVar(string $name, mixed $value): static
    {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * Merge multiple variables into the view's variable set.
     *
     * Later values override earlier ones for the same keys.
     *
     * @param array $vars Associative array of variables.
     * @return $this
     */
    public function setVars(array $vars): static
    {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    /**
     * Render a view (and optional layout) and return the result.
     *
     * @param string $view View name to render.
     * @param array $vars Additional variables for this render call.
     * @return string Rendered content.
     */
    public function render(string $view, array $vars = []): string
    {
        $content = $this->renderPartial($view, $vars);

        if ($this->layout !== null && $this->renderDepth === 0) {
            $content = $this->renderLayout($this->layout, $content);
        }

        return $content;
    }

    /**
     * Render a partial view template and return the generated output.
     *
     * This method extracts variables into the local scope of the template
     * and captures the output buffer.
     *
     * @param string $view View name to resolve and render.
     * @param array $vars Variables for this render call.
     * @return string Rendered HTML/output.
     * @throws Exception If the view file cannot be resolved.
     */
    public function renderPartial(string $view, array $vars = []): string
    {
        $file = $this->resolveView($view);
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $file");
        }

        $this->renderDepth++;
        extract(array_merge($this->vars, $vars), EXTR_SKIP);

        ob_start();
        include $file;
        $output = ob_get_clean();

        $this->renderDepth--;
        return $output;
    }

    /**
     * Render a layout template wrapping provided content.
     *
     * The layout receives the content in the `content` variable.
     *
     * @param string $layout Layout view name.
     * @param string $content Previously rendered content.
     * @param array $vars Additional variables to pass to the layout.
     * @return string Rendered layout output.
     */
    public function renderLayout(string $layout, string $content, array $vars = []): string
    {
        $vars['content'] = $content;
        return $this->renderPartial($layout, $vars);
    }

    /**
     * Get current render nesting depth. Useful to detect top-level renders
     * (depth 0) when deciding whether to apply a layout.
     *
     * @return int Current render depth (0 = top-level).
     */
    public function getRenderDepth(): int
    {
        return $this->renderDepth;
    }


    /**
     * Resolve a view name to an actual file path on the filesystem.
     * @param string $view View name to resolve.
     * @throws \RuntimeException If the view cannot be resolved.
     * @return string Resolved file path.
     */
    protected function resolveView(string $view): string
    {
        if ($view === '') {
            throw new \RuntimeException("Empty view name");
        }

        $ns = \strstr($view, '::', true);
        if ($ns !== false) {
            // namespaced view
            $name = \substr($view, \strlen($ns) + 2);

            if (!isset($this->namespaces[$ns])) {
                throw new \RuntimeException("Unknown view namespace: $ns");
            }

            return $this->namespaces[$ns] . '/' . \str_replace('.', '/', $name) . $this->extension;
        }

        $len = \strlen($view);

        if ($view[0] === '/') {
            // absolute unix path
            $path = $view;

        } elseif ($view[1] === ':' && $len >= 3 && ($view[2] === '/' || $view[2] === '\\') && \ctype_alpha($view[0])) {
            // absolute windows path: C:/foo or C:\foo
            $path = $view;

        } elseif ($view[0] === '\\' && $len >= 2 && $view[1] === '\\') {
            // absolute UNC path: \\server\share
            $path = $view;

        } elseif ($view[0] === '.') {
            // treat as literal relative path: ./partials/header or ../shared/footer
            $path = $this->path . '/' . $view;

        } else {
            // relative view name, resolve to path using dot-notation
            $relative = \str_replace('.', '/', $view);
            $path = $this->path . '/' . $relative;
        }

        if (!\str_ends_with($path, $this->extension)) {
            $path .= $this->extension;
        }
        return $path;
    }
}
