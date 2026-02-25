<?php

namespace Merlin\Cli;

use ReflectionClass;

class Console
{
    protected array $namespaces = ['Merlin\\Cli\\Tasks'];
    protected array $taskPaths = [];
    protected array $tasks = []; // taskName => class
    protected string $scriptName;
    protected bool $coerceParams = false;
    protected bool $colors;

    protected const ANSI = [
        'reset' => "\033[0m",
        'bold' => "\033[1m",
        'dim' => "\033[2m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'gray' => "\033[90m",
        'bred' => "\033[91m",
        'bgreen' => "\033[92m",
        'byellow' => "\033[93m",
        'bblue' => "\033[94m",
        'bmagenta' => "\033[95m",
        'bcyan' => "\033[96m",
        'bg-black' => "\033[40m",
        'bg-red' => "\033[41m",
        'bg-green' => "\033[42m",
        'bg-yellow' => "\033[43m",
        'bg-blue' => "\033[44m",
        'bg-magenta' => "\033[45m",
        'bg-cyan' => "\033[46m",
        'bg-white' => "\033[47m",
    ];
    protected string $defaultAction = "indexAction";

    public function __construct(string $scriptName = null)
    {
        $this->scriptName = $scriptName ?? basename($_SERVER['argv'][0] ?? 'console.php');
        $this->colors = $this->detectColorSupport();
    }

    public function addNamespace(string $ns): void
    {
        $ns = trim($ns, '\\');
        if (!in_array($ns, $this->namespaces, true)) {
            $this->namespaces[] = $ns;
        }
    }

    public function addTaskPath(string $path, bool $registerAutoload = false): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        if (!in_array($path, $this->taskPaths, true)) {
            $this->taskPaths[] = $path;
            if ($registerAutoload) {
                $this->registerSimpleAutoload($path);
            }
        }
    }

    /**
     * Get the default action method name used when no action is specified on the command line.
     *
     * @return string Default action method name (without namespace), e.g. "indexAction".
     */
    public function getDefaultAction(): string
    {
        return $this->defaultAction;
    }

    /**
     * Set the default action method name used when no action is specified on the command line.
     *
     * @param string $defaultAction Action method name, e.g. "indexAction".
     * @throws \InvalidArgumentException If the given name is empty.
     */
    public function setDefaultAction(string $defaultAction): void
    {
        if (empty($defaultAction)) {
            throw new \InvalidArgumentException("Default action cannot be empty");
        }
        $this->defaultAction = $defaultAction;
    }

    // -------------------------------------------------------------------------
    //  Color / output helpers
    // -------------------------------------------------------------------------

    protected function detectColorSupport(): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return function_exists('sapi_windows_vt100_support')
                && @sapi_windows_vt100_support(STDOUT);
        }
        return function_exists('stream_isatty') && stream_isatty(STDOUT);
    }

    /**
     * Enable or disable ANSI color output explicitly.
     */
    public function enableColors(bool $colors): void
    {
        $this->colors = $colors;
    }

    /** Check whether ANSI color output is enabled. */
    public function hasColors(): bool
    {
        return $this->colors;
    }

    /**
     * Apply one or more named ANSI styles to a string.
     * Style names: bold, dim, red, green, yellow, blue, magenta, cyan, white, gray,
     *              bred, bgreen, byellow, bcyan
     *
     * When color support is disabled, the text is returned unchanged.
     */
    public function style(string $text, string ...$styles): string
    {
        if (!$this->colors || empty($styles)) {
            return $text;
        }
        $open = '';
        foreach ($styles as $s) {
            $open .= self::ANSI[$s] ?? '';
        }
        return $open . $text . self::ANSI['reset'];
    }

    /** Write a line to stdout (newline appended). */
    public function writeln(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    /** Plain informational line. */
    public function line(string $text): void
    {
        $this->writeln($text);
    }

    /** Success message (bright green). */
    public function success(string $text): void
    {
        $this->writeln($this->style($text, 'bgreen'));
    }

    /** Warning message (bright yellow). */
    public function warn(string $text): void
    {
        $this->writeln($this->style($text, 'byellow'));
    }

    /** Error message (bright red). */
    public function error(string $text): void
    {
        $this->writeln($this->style('[ERROR] ', 'bred', 'bold') . $text);
    }

    /** Critical message (red on white bg). */
    public function critical(string $text): void
    {
        $this->writeln($this->style('[CRITICAL] ', 'red', 'bg-white', 'bold') . $text);
    }

    /** Muted / dimmed text. */
    public function muted(string $text): void
    {
        $this->writeln($this->style($text, 'gray'));
    }

    /** Informational message (cyan). */
    public function info(string $text): void
    {
        $this->writeln($this->style($text, 'cyan'));
    }

    // -------------------------------------------------------------------------
    //  Public API
    // -------------------------------------------------------------------------

    /**
     * Check whether automatic parameter type coercion is enabled.
     *
     * When enabled, string arguments that look like integers, floats, booleans,
     * or NULL are converted to the corresponding PHP scalar before being passed
     * to the action method.
     *
     * @return bool True if parameter coercion is enabled.
     */
    public function shouldCoerceParams(): bool
    {
        return $this->coerceParams;
    }

    /**
     * Enable or disable automatic parameter type coercion.
     *
     * @param bool $coerceParams True to enable coercion, false to pass all arguments as strings.
     */
    public function setCoerceParams(bool $coerceParams): void
    {
        $this->coerceParams = $coerceParams;
    }

    /**
     * Process the given task, action, and parameters.
     *
     * @param string|null $task The name of the task to execute.
     * @param string|null $action The name of the action to execute within the task.
     * @param array $params An array of parameters to pass to the action method.
     */
    public function process(?string $task = null, ?string $action = null, array $params = []): void
    {
        $this->autodiscover();
        $this->registerBuiltInHelp();

        // If no task provided, show overview
        if (!$task) {
            $this->helpOverview();
            return;
        }

        // help handling
        if ($task === 'help') {
            $target = $action ?? null;
            if ($target) {
                $this->helpTask($target);
            } else {
                $this->helpOverview();
            }
            return;
        }

        // run the requested task/action
        $this->dispatch($task, $action, $params);
    }

    protected function dispatch(string $taskName, ?string $actionName, array $params): void
    {
        // normalize task name
        $taskKey = strtolower($taskName);

        if (!isset($this->tasks[$taskKey])) {
            echo "Task '{$taskName}' not found. Run '{$this->scriptName} help' for available tasks.\n";
            return;
        }

        $class = $this->tasks[$taskKey];
        if (!class_exists($class)) {
            echo "Task class '{$class}' not loadable.\n";
            return;
        }

        $task = new $class();
        if (!$task instanceof Task) {
            echo "Task class '{$class}' is not a valid Task.\n";
            return;
        }

        // determine method name
        $method = $this->actionToMethod($actionName);

        if (!method_exists($task, $method)) {
            // try default action fallback
            if (
                method_exists(
                    $task,
                    $this->defaultAction
                )
            ) {
                $method = $this->defaultAction;
            } else {
                echo "Action '" . ($actionName ?? '') . "' not found on task '{$taskName}'.\n";
                $this->helpTask($taskKey);
                return;
            }
        }

        // call method with params
        [$params, $options] = $this->splitArgs($params);
        $task->options = $options;
        $task->console = $this;
        $task->$method(...$params);
    }

    protected function actionToMethod(?string $action): string
    {
        if (!$action) {
            return $this->defaultAction;
        }

        // convert dashed or colon or snake to camelCase then append Action
        $action = str_replace(':', '-', $action);
        $action = str_replace('_', '-', $action);
        $parts = explode('-', $action);
        $camel = array_shift($parts);
        foreach ($parts as $p) {
            $camel .= ucfirst($p);
        }
        return $camel . 'Action';
    }

    /** Autodiscover tasks in all registered namespaces and paths */
    public function autodiscover(): void
    {
        foreach ($this->namespaces as $ns) {
            $this->discoverNamespaceViaComposer($ns);
        }

        $this->discoverComposerNamespaces();

        foreach ($this->taskPaths as $path) {
            $this->discoverPath($path);
        }
    }

    protected function discoverNamespaceViaComposer(string $ns): void
    {
        $map = $this->readComposerPsr4();
        $nsClean = rtrim($ns, '\\');

        // Find the longest matching PSR-4 prefix for this namespace
        $bestPrefix = null;
        $bestDir = null;
        foreach ($map as $prefix => $dir) {
            $prefixClean = rtrim($prefix, '\\');
            if ($nsClean === $prefixClean || str_starts_with($nsClean . '\\', $prefixClean . '\\')) {
                if ($bestPrefix === null || strlen($prefixClean) > strlen($bestPrefix)) {
                    $bestPrefix = $prefixClean;
                    $bestDir = $dir;
                }
            }
        }

        if ($bestPrefix !== null) {
            // Derive the exact subdirectory from the namespace suffix
            $suffix = ltrim(substr($nsClean, strlen($bestPrefix)), '\\');
            $path = $suffix
                ? $bestDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $suffix)
                : $bestDir;
            if (is_dir($path)) {
                $this->discoverPath($path);
            }
            return;
        }

        // Fallback: try to locate a directory matching the namespace under cwd
        $guess = getcwd() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $nsClean);
        if (is_dir($guess)) {
            $this->discoverPath($guess);
        }
    }

    protected function discoverComposerNamespaces(): void
    {
        $map = $this->readComposerPsr4();
        foreach ($map as $dir) {
            // Recursively find every *Task.php under this PSR-4 root
            $this->discoverPathRecursive($dir);
        }
    }

    protected function discoverPathRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Task.php')) {
                $this->registerTaskFile($file->getPathname());
            }
        }
    }

    protected function readComposerPsr4(): array
    {
        $composerDir = $this->findComposerRoot();
        if ($composerDir === null) {
            return [];
        }
        $json = json_decode(file_get_contents($composerDir . DIRECTORY_SEPARATOR . 'composer.json'), true);
        $raw = $json['autoload']['psr-4'] ?? [];
        // Resolve all directories to absolute paths
        $result = [];
        foreach ($raw as $ns => $dir) {
            $result[$ns] = rtrim($composerDir . DIRECTORY_SEPARATOR . ltrim($dir, '/\\'), DIRECTORY_SEPARATOR);
        }
        return $result;
    }

    protected function findComposerRoot(): ?string
    {
        // Walk up from the directory of this file until we find composer.json
        $dir = __DIR__;
        while (true) {
            if (is_file($dir . DIRECTORY_SEPARATOR . 'composer.json')) {
                return $dir;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }
        // Fallback: cwd
        if (is_file(getcwd() . DIRECTORY_SEPARATOR . 'composer.json')) {
            return getcwd();
        }
        return null;
    }

    protected function discoverPath(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . DIRECTORY_SEPARATOR . '*Task.php') ?: [] as $file) {
            $this->registerTaskFile($file);
        }
    }

    /**
     * Parse the namespace and class name directly from file content, then register
     * the task if it is a valid Task subclass. This avoids any path/namespace guessing.
     */
    protected function registerTaskFile(string $file): void
    {
        $class = $this->resolveClassFromFile($file);
        if (!$class) {
            return;
        }
        if (!class_exists($class)) {
            require_once $file;
        }
        if (class_exists($class) && is_subclass_of($class, Task::class)) {
            $taskName = $this->taskNameFromClass($class);
            if (!isset($this->tasks[$taskName])) {
                $this->tasks[$taskName] = $class;
            }
        }
    }

    protected function resolveClassFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        if (!$content) {
            return null;
        }
        $base = basename($file, '.php');
        if (preg_match('/^\s*namespace\s+([^;]+);/m', $content, $m)) {
            return trim($m[1]) . '\\' . $base;
        }
        return null;
    }

    protected function taskNameFromClass(string $class): string
    {
        $short = (new ReflectionClass($class))->getShortName();
        $short = preg_replace('/Task$/', '', $short);
        $parts = preg_split('/(?=[A-Z])/', $short, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_map(fn($p) => strtolower($p), $parts);
        return $parts[0] ?? strtolower($short);
    }

    protected function registerBuiltInHelp(): void
    {
        $this->tasks['help'] = self::class;
    }

    /** Built-in help task */
    public function helpOverview(): void
    {
        $this->writeln();
        $this->writeln("Usage: $this->scriptName <task> <action> [args...]");
        $this->writeln();
        $this->writeln($this->style('Available tasks and actions:', 'bold', 'white'));
        $termWidth = $this->terminalWidth();
        foreach ($this->tasks as $name => $class) {
            if ($name === 'help') {
                continue;
            }
            $this->writeln();
            $desc = $this->extractShortDescription($class);

            // Task label column
            $labelWidth = 20;
            $leftPad = 2; // leading spaces printed before label
            $avail = max(10, $termWidth - $leftPad - $labelWidth - 1);
            $descLines = $this->wrapText($desc, $avail);

            $labelStyled = $this->style(str_pad($name, $labelWidth), 'bcyan', 'bold');
            if (count($descLines) > 0 && $descLines[0] !== '') {
                $this->writeln('  ' . $labelStyled . ' ' . $this->style($descLines[0], 'bold'));
            } else {
                $this->writeln('  ' . $labelStyled);
            }

            // remaining wrapped description lines (align under description column)
            for ($i = 1; $i < count($descLines); $i++) {
                $this->writeln('  ' . str_repeat(' ', $labelWidth) . ' ' . $this->style($descLines[$i], 'bold'));
            }

            // Actions: action column is printed with 4 leading spaces + 2 spaces before the name
            $actionLabelInner = 16; // the str_pad width used for actions
            $actionLeft = 4 + 2; // visual indent
            $actionAvail = max(10, $termWidth - $actionLeft - $actionLabelInner - 1);
            foreach ($this->extractActionDescriptions($class) as $action => $actionDesc) {
                if ($actionDesc === '') {
                    $this->writeln(
                        $this->style('    ', 'dim')
                        . $this->style('  ' . str_pad($action, $actionLabelInner), 'bgreen', 'bold')
                    );
                    continue;
                }

                $actionLines = $this->wrapText($actionDesc, $actionAvail);
                $first = array_shift($actionLines);
                $this->writeln(
                    $this->style('    ', 'dim')
                    . $this->style('  ' . str_pad($action, $actionLabelInner), 'bgreen', 'bold')
                    . ' ' . $this->style($first)
                );
                foreach ($actionLines as $ln) {
                    $this->writeln($this->style('    ', 'dim') . str_repeat(' ', $actionLabelInner + 2) . ' ' . $this->style($ln));
                }
            }
        }
        $this->writeln();
        $this->writeln($this->style('Run "' . $this->scriptName . ' help <task>" for details.'));
        $this->writeln();
    }

    public function helpTask(string $task): void
    {
        $taskKey = strtolower($task);
        $class = $this->tasks[$taskKey] ?? null;
        if (!$class) {
            $this->error("Task '{$task}' not found.");
            return;
        }

        $termWidth = $this->terminalWidth();

        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment() ?: '';
        $info = static::parseDocComment($doc, $this->scriptName);

        $this->writeln();
        $this->writeln($this->style('Task: ', 'green', 'bold') . $this->style($taskKey, 'bcyan', 'bold'));
        //$this->writeln('      ' . $this->style(str_repeat('─', strlen($taskKey)), 'cyan'));
        $this->writeln();
        $this->writeln($info['description']);
        $this->writeln();

        // list available actions
        $actions = $this->extractActionDescriptions($class);

        if (!empty($actions)) {
            $this->writeln($this->style('Actions:', 'bold', 'green'));

            $actionLabelInner = 16;
            $leadingSpaces = 2; // two leading spaces before task
            // description starts after: leading + task + ' ' + actionLabel + ' '
            $descStartCol = $leadingSpaces + strlen($taskKey) + 1 + $actionLabelInner + 1;
            $actionAvail = max(10, $termWidth - $descStartCol);

            $addNewLine = false;
            foreach ($actions as $action => $actionDesc) {
                if ($addNewLine) {
                    $this->writeln();
                } else {
                    $addNewLine = true;
                }
                $lines = $this->wrapText($actionDesc, $actionAvail);
                $first = array_shift($lines);

                $this->writeln(
                    str_repeat(' ', $leadingSpaces)
                    . $this->style($taskKey, 'white', 'bold')
                    . ' '
                    . $this->style(str_pad($action, $actionLabelInner), 'bcyan', 'bold')
                    . ($first !== '' ? ' ' . $this->style($first) : '')
                );

                // continuation lines: indent to description column
                $continuationIndent = str_repeat(' ', $descStartCol);
                foreach ($lines as $ln) {
                    $this->writeln($continuationIndent . $this->style($ln));
                }

            }
            $this->writeln();
        }

        $this->writeln($this->style('Usage:', 'bold', 'green'));
        $actionsList = implode('|', array_keys($actions)) ?: '<action>';
        $this->writeln('  ' . $this->style('php ' . $this->scriptName, 'dim') . ' ' . $this->style($taskKey, 'white', 'bold') . ' ' . $this->style($actionsList, 'bcyan', 'bold') . ' [args...]');
        if ($info['usage']) {
            $this->renderUsageBlock($info['usage'], $taskKey, $termWidth);
        }
        $this->writeln();

        if ($info['options']) {
            $this->writeln($this->style('Options:', 'bold', 'yellow'));
            foreach (explode("\n", $info['options']) as $l) {
                $this->writeln($this->style($l, 'gray'));
            }
            $this->writeln();
        }

        if ($info['examples']) {
            $this->writeln($this->style('Examples:', 'bold', 'green'));
            foreach (explode("\n", $info['examples']) as $l) {
                $this->writeln($this->highlightCommandLine($l, $taskKey));
            }
            $this->writeln();
        }
    }

    /**
     * Syntax-highlight a single command line in a Usage or Examples block.
     *
     * Token colouring rules:
     *   interpreter (php)   → dim
     *   script name         → dim
     *   task name           → bold white
     *   action name         → bold cyan
     *   <placeholder>       → bright yellow
     *   [--option]          → green brackets, highlighted inner token
     *   --flag / --key=val  → green (val placeholder stays bright yellow)
     *   # comment           → gray
     *   positional arg      → white
     *   continuation lines  → only option/arg tokens (no interpreter prefix)
     */
    protected function highlightCommandLine(string $line, ?string $taskName = null): string
    {
        if (!$this->colors) {
            return $line;
        }

        // Strip trailing CR/LF so Windows \r doesn't corrupt the last token
        $line = rtrim($line);

        // Preserve leading indentation
        $trimmed = ltrim($line);
        $indent = substr($line, 0, strlen($line) - strlen($trimmed));

        if ($trimmed === '') {
            return $line;
        }

        // Split into (word, whitespace, word, whitespace …) keeping delimiters
        $parts = preg_split('/( +)/', $trimmed, -1, PREG_SPLIT_DELIM_CAPTURE);

        // Detect whether this is a command line (starts with interpreter) or a
        // continuation line (starts with options / placeholders)
        $firstWord = $parts[0] ?? '';
        $isCommand = (bool) preg_match('/^php\d*(?:\.exe)?$/i', $firstWord);

        $result = $indent;
        $wordIndex = 0; // counts only non-whitespace tokens
        $inComment = false;

        foreach ($parts as $part) {
            // Whitespace between tokens – pass through unchanged
            if ($part !== '' && $part[0] === ' ') {
                $result .= $part;
                continue;
            }

            if ($inComment) {
                $result .= $this->style($part, 'gray');
                continue;
            }

            if ($part === '') {
                continue;
            }

            // Comment marker
            if ($part[0] === '#') {
                $inComment = true;
                $result .= $this->style($part, 'gray');
                $wordIndex++;
                continue;
            }

            if ($isCommand) {
                $result .= match ($wordIndex) {
                    0 => $this->style($part, 'dim'),                   // php
                    1 => $this->style($part, 'dim'),                   // script
                    2 => $this->style($part, 'bold', 'white'),         // task
                    3 => $this->style($part, 'bold', 'bcyan'),         // action
                    default => $this->highlightCliToken($part),
                };
            } else {
                $result .= $this->highlightCliToken($part);
            }

            $wordIndex++;
        }

        return $result;
    }

    /**
     * Colour a single CLI token: option, placeholder, or positional argument.
     */
    protected function highlightCliToken(string $token): string
    {
        if ($token === '') {
            return '';
        }

        // [--option], [--key=<val>], [<placeholder>] …
        if ($token[0] === '[' && str_ends_with($token, ']')) {
            $inner = substr($token, 1, -1);
            return $this->style('[', 'bmagenta')
                . $this->highlightCliToken($inner)
                . $this->style(']', 'bmagenta');
        }

        // <placeholder> or <a|b|c>
        if ($token[0] === '<' && str_ends_with($token, '>')) {
            return $this->style($token, 'white');
        }

        // --flag or --key=<val> or --key=literal
        if (str_starts_with($token, '--') || (strlen($token) === 2 && $token[0] === '-')) {
            if (str_contains($token, '=')) {
                [$flag, $val] = explode('=', $token, 2);
                $coloredVal = ($val !== '' && $val[0] === '<')
                    ? $this->style($val, 'white')
                    : $this->style($val, 'white');
                return $this->style($flag . '=', 'bmagenta') . $coloredVal;
            }
            return $this->style($token, 'bmagenta');
        }

        // short option -f
        if (strlen($token) >= 2 && $token[0] === '-') {
            return $this->style($token, 'bmagenta');
        }

        // Plain positional argument (e.g. src/Models, User.php)
        return $this->style($token, 'white');
    }

    protected function methodToActionName(string $method): string
    {
        $base = preg_replace('/Action$/', '', $method);
        // convert camelCase to dashed
        $dashed = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $base));
        return $dashed;
    }

    protected function extractShortDescription(?string $class): string
    {
        if (!$class || !class_exists($class)) {
            return '';
        }
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment() ?: '';
        $info = static::parseDocComment($doc, $this->scriptName);
        return $info['description'] ? strtok($info['description'], "\n") : '';
    }

    /**
     * Returns an ordered map of action-name => one-line description for all
     * public *Action methods on the given class. Lines starting with @param
     * (and everything after) are stripped; only the opening prose is kept.
     *
     * @return array<string, string>
     */
    protected function extractActionDescriptions(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }
        $ref = new ReflectionClass($class);
        $actions = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if (!preg_match('/^([a-zA-Z0-9_]+)Action$/', $m->getName(), $mm)) {
                continue;
            }
            $actionName = $this->methodToActionName($m->getName());
            $doc = $m->getDocComment() ?: '';
            $actions[$actionName] = $this->extractDocHeader($doc);
        }
        return $actions;
    }

    /**
     * Extract the opening prose from a doc comment, stopping at the first
     * @-tag line. All consecutive prose lines are joined into one
     * string so wrapped sentences come out complete.
     */
    protected function extractDocHeader(string $doc): string
    {
        if ($doc === '') {
            return '';
        }
        // Strip /** ... */ wrapper and leading " * "
        $doc = trim(preg_replace('/^\/\*\*|\*\/$/', '', $doc));
        $doc = preg_replace('/^\s*\*\s?/m', '', $doc);

        $parts = [];
        foreach (explode("\n", $doc) as $line) {
            $line = rtrim($line, "\r");
            $trim = trim($line);
            if ($trim === '' || $trim[0] === '@') {
                break; // stop at blank line or first @tag
            }
            $parts[] = $trim;
        }
        return implode(' ', $parts);
    }

    protected function registerSimpleAutoload(string $path): void
    {
        spl_autoload_register(function ($class) use ($path) {
            $parts = explode('\\', $class);
            $file = $path . DIRECTORY_SEPARATOR . end($parts) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    protected function splitArgs(array $args): array
    {
        $options = [];
        $params = [];

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            // long option: --foo or --foo=bar
            if (str_starts_with($arg, '--')) {
                $opt = substr($arg, 2);
                if (str_contains($opt, '=')) {
                    [$key, $value] = explode('=', $opt, 2);
                    $options[$key] = $this->coerceParam($value);
                } else {
                    // next argument is value or it's a flag
                    $next = $args[$i + 1] ?? null;
                    if ($next !== null && !str_starts_with($next, '-')) {
                        $options[$opt] = $this->coerceParam($next);
                        $i++;
                    } elseif (str_starts_with($opt, 'no-')) {
                        $options[substr($opt, 3)] = false;
                    } else {
                        $options[$opt] = true;
                    }
                }
                continue;
            }

            // short option: -f or -f=bar
            if (str_starts_with($arg, '-')) {
                $opt = substr($arg, 1);
                if (str_contains($opt, '=')) {
                    [$key, $value] = explode('=', $opt, 2);
                    $options[$key] = $this->coerceParam($value);
                } else {
                    $next = $args[$i + 1] ?? null;
                    if ($next !== null && !str_starts_with($next, '-')) {
                        $options[$opt] = $this->coerceParam($next);
                        $i++;
                    } else {
                        $options[$opt] = true;
                    }
                }
                continue;
            }

            // normal argument
            $params[] = $arg;
        }

        return [$params, $options];
    }

    /**
     * Coerce a string parameter to int, float, bool, or null if it looks like one of those.
     * Otherwise return the original string. Empty string is returned as-is.
     * @param string $param The parameter string to coerce.
     * @return int|float|bool|null|string The coerced value, or original string if no coercion applied.
     */
    public function coerceParam(string $param): int|float|bool|null|string
    {
        static $boolMap = [
        'true' => true,
        'on' => true,
        'false' => false,
        'off' => false,
        'null' => null,
        ];

        if (!$this->coerceParams) {
            return $param;
        }

        if ($param === '') {
            return '';
        }

        $lower = strtolower($param);

        // boolean/null
        if (isset($boolMap[$lower])) {
            return $boolMap[$lower];
        }

        if ($param[0] === '0') {
            // leading zero means string (to preserve things like "0123")
            return $param;
        }

        // integer
        if (preg_match('/^-?\d+$/', $param)) {
            return (int) $param;
        }

        // float
        if (preg_match('/^-?\d+\.\d+$/', $param)) {
            return (float) $param;
        }

        return $param;
    }

    protected static function parseDocComment(string $doc, string $scriptName): array
    {
        $doc = trim(preg_replace('/^\/\*\*|\*\/$/', '', $doc));
        $doc = preg_replace('/^\s*\*\s?/m', '', $doc);
        $doc = str_replace('console.php', $scriptName, $doc);
        $sections = ['description' => '', 'usage' => '', 'options' => '', 'examples' => '',];
        $current = 'description';
        $doc = str_replace("\r", '', $doc);
        foreach (explode("\n", $doc) as $line) {
            $trim = trim($line);
            if ($trim === '') {
                $sections[$current] .= "\n";
                continue;
            }
            if (stripos($trim, 'Usage:') === 0) {
                $current = 'usage';
                continue;
            }
            if (stripos($trim, 'Options:') === 0) {
                $current = 'options';
                continue;
            }
            if (stripos($trim, 'Examples:') === 0) {
                $current = 'examples';
                continue;
            }
            if ($current === 'description') {
                $line .= " ";
            } else {
                $line .= "\n";
            }
            $sections[$current] .= $line;
        }
        foreach ($sections as $key => $s) {
            $sections[$key] = rtrim($s);
        }
        return $sections;
    }

    /**
     * Return detected terminal width (columns). Falls back to 80.
     */
    public function terminalWidth(): int
    {
        static $w = null;
        if ($w !== null) {
            return $w;
        }

        $default = 80;

        // 1) ENV
        $cols = getenv('COLUMNS');
        if ($cols !== false && (int) $cols > 0) {
            $w = (int) $cols;
            return $w;
        }

        // 2) tput (Unix)
        if (function_exists('exec')) {
            // only try tput when we are in a real terminal
            if (function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
                $out = [];
                @exec('tput cols 2>/dev/null', $out);
                if (!empty($out) && is_array($out) && (int) $out[0] > 0) {
                    $w = (int) $out[0];
                    return $w;
                }
            }

            // 3) Windows: try to parse "mode CON" output for "Columns: N"
            if (stripos(PHP_OS, 'WIN') === 0) {
                $out = [];
                @exec('mode CON 2>&1', $out);
                if (!empty($out) && is_array($out)) {
                    $columnPos = 1; // usually the second number in the line "Columns: 120"
                    foreach ($out as $line) {
                        if (preg_match('/\b(\d{2,4})\b/', $line, $m)) {
                            if ($columnPos-- > 0) {
                                continue; // skip until we reach the column number
                            }
                            $w = (int) $m[1];
                            return $w;
                        }
                    }
                }
            }
        }

        $w = $default;
        return $w;
    }


    /**
     * Parse and render a Usage block:
     *  - Lines starting with 'php' or the task name open a new usage entry.
     *  - Lines starting with '[', '<', or '-' are continuations of the current entry.
     *  - All other non-blank lines are treated as prose and rendered inline.
     *  - Argument columns across all entries are aligned to the longest left side.
     */
    protected function renderUsageBlock(string $usageText, string $taskKey, int $termWidth): void
    {
        $taskPattern = '/^' . preg_quote($taskKey, '/') . '\b/i';

        // ── Pass 1: split into 'entry' and 'prose' items ─────────────────────
        $items = [];
        $currentEntry = null;
        $emptyLine = false;

        foreach (explode("\n", $usageText) as $ln) {
            $ln = rtrim($ln);
            $trim = ltrim($ln);

            if ($trim === '') {
                if ($currentEntry !== null) {
                    $items[] = ['type' => 'entry', 'text' => $currentEntry];
                    $currentEntry = null;
                }
                $emptyLine = true;
                continue;
            }

            $trim = preg_replace('/^php\d*(?:\.exe)?\b\s*/i', '', $trim);
            $trim = preg_replace('/^\w+\.php\b\s*/i', '', $trim);
            $isEntryStart = (bool) preg_match('/^php\d*(?:\.exe)?\b/i', $trim)
                || (bool) preg_match($taskPattern, $trim);
            $isContinuation = (bool) preg_match('/^[\[<\-]/', $trim);

            if ($isEntryStart) {
                if ($currentEntry !== null) {
                    $items[] = ['type' => 'entry', 'text' => $currentEntry];
                }
                $currentEntry = $trim;
            } elseif ($isContinuation && $currentEntry !== null) {
                $currentEntry .= ' ' . $trim;
            } else {
                if ($currentEntry !== null) {
                    $items[] = ['type' => 'entry', 'text' => $currentEntry];
                    $currentEntry = null;
                }
                if (!$emptyLine && !empty($items) && end($items)['type'] === 'prose') {
                    // append to previous prose block
                    $items[count($items) - 1]['text'] .= ' ' . $trim;
                } else {
                    $items[] = ['type' => 'prose', 'text' => $trim];
                }
            }
            $emptyLine = false;
        }
        if ($currentEntry !== null) {
            $items[] = ['type' => 'entry', 'text' => $currentEntry];
        }

        // ── Pass 2: parse each entry, find max left-column width ─────────────
        $maxLeftLen = 0;
        foreach ($items as &$item) {
            if ($item['type'] !== 'entry') {
                continue;
            }
            $parts = preg_split('/\s+/', $item['text']);
            $isPhp = (bool) preg_match('/^php\d*(?:\.exe)?$/i', $parts[0]);
            $actionIdx = $isPhp ? 3 : 1;
            $actionIdx = min($actionIdx, count($parts) - 1);
            $leftParts = array_slice($parts, 0, $actionIdx + 1);
            $restParts = array_slice($parts, $actionIdx + 1);
            $leftPlain = implode(' ', $leftParts);

            $item['isPhp'] = $isPhp;
            $item['leftParts'] = $leftParts;
            $item['leftPlain'] = $leftPlain;
            $item['rest'] = implode(' ', $restParts);

            $maxLeftLen = max($maxLeftLen, strlen($leftPlain));
        }
        unset($item);

        // ── Pass 3: render ───────────────────────────────────────────────────
        $leadingSpaces = 2;
        $descStartCol = $leadingSpaces + $maxLeftLen + 1;
        $argAvail = max(10, $termWidth - $descStartCol);
        $contIndent = str_repeat(' ', $descStartCol);
        $addEmptyLine = true;

        foreach ($items as $item) {

            if ($item['type'] === 'prose') {
                if ($addEmptyLine) {
                    $this->writeln();
                    $addEmptyLine = false;
                }
                $this->writeln(str_repeat(' ', $leadingSpaces) . $this->style($item['text'], 'dim'));
                continue;
            }

            $addEmptyLine = true;
            $this->writeln();

            // Style left tokens
            $leftStyled = [];
            foreach ($item['leftParts'] as $i => $tok) {
                if ($item['isPhp']) {
                    $leftStyled[] = match ($i) {
                        0, 1 => $this->style($tok, 'dim'),
                        2 => $this->style($tok, 'bold', 'white'),
                        default => $this->style($tok, 'bold', 'bcyan'),
                    };
                } else {
                    $leftStyled[] = $i === 0
                        ? $this->style($tok, 'bold', 'white')
                        : $this->style($tok, 'bold', 'bcyan');
                }
            }

            // Pad left side so all args start at the same column
            $padding = str_repeat(' ', $maxLeftLen - strlen($item['leftPlain']));
            $argLines = $this->wrapText($item['rest'], $argAvail);
            $firstArg = array_shift($argLines);

            $this->writeln(
                str_repeat(' ', $leadingSpaces)
                . implode(' ', $leftStyled)
                . $padding
                . ($firstArg !== '' ? ' ' . $this->highlightCommandLine($firstArg) : '')
            );
            foreach ($argLines as $al) {
                $this->writeln($contIndent . $this->highlightCommandLine($al));
            }
        }
    }

    /**
     * Word-wrap a text block into an array of lines for the given column width.
     * Lines are trimmed of trailing whitespace. Empty input returns an array with one empty string.
     * @param string $text The text to wrap.
     * @param int $width The maximum column width for wrapping.
     */
    public function wrapText(string $text, int $width): array
    {
        $width = max(10, (int) $width);
        if ($text === '') {
            return [''];
        }
        $wrapped = wordwrap($text, $width, "\n");
        $lines = explode("\n", $wrapped);
        return array_map(fn($l) => rtrim($l), $lines);
    }

}
