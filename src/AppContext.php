<?php
namespace Merlin;

use RuntimeException;
use Merlin\Db\Database;
use Merlin\Http\Cookies;
use Merlin\Http\Session;
use Merlin\Mvc\ViewEngine;
use Merlin\Db\DatabaseManager;
use Merlin\Http\Request as HttpRequest;

class AppContext
{
    public function __construct()
    {
        $this->registerDefaultServices();
    }

    protected function registerDefaultServices(): void
    {
        $this->services = [
            Session::class => fn() => $this->session(),
            Cookies::class => fn() => $this->cookies(),
            HttpRequest::class => fn() => $this->request(),
            ViewEngine::class => fn() => $this->view(),
            DatabaseManager::class => fn() => $this->dbManager(),
            AppContext::class => fn() => $this,
        ];
    }

    protected array $services = [];

    protected ?HttpRequest $request = null;

    protected ?ViewEngine $view = null;

    protected ?Session $session = null;

    protected ?Cookies $cookies = null;

    protected ?ResolvedRoute $route = null;

    protected DatabaseManager $dbManager;

    // --- Singleton ---

    /** @var AppContext|null The singleton instance of AppContext. */
    protected static ?AppContext $instance = null;

    /**
     * Get the singleton instance of AppContext. If it doesn't exist, it will be created.
     *
     * @return static The singleton instance of AppContext.
     */
    public static function instance(): static
    {
        // Falls der Router/Bootstrap noch nichts gesetzt hat:
        return self::$instance ??= new static();
    }

    /**
     * Set the singleton instance of AppContext. This can be used to inject a custom context, for example in tests.
     *
     * @param AppContext $instance The AppContext instance to set as the singleton.
     */
    public static function setInstance(AppContext $instance): void
    {
        self::$instance = $instance;
    }

    // --- Lazy Services ---

    /**
     * Get the HttpRequest instance. If it doesn't exist, it will be created.
     *
     * @return HttpRequest The HttpRequest instance.
     */
    public function request(): HttpRequest
    {
        return $this->request ??= new HttpRequest();
    }

    /**
     * Get the ViewEngine instance. If it doesn't exist, it will be created.
     *
     * @return ViewEngine The ViewEngine instance.
     */
    public function view(): ViewEngine
    {
        return $this->view ??= new ViewEngine();
    }

    /**
     * Get the Cookies instance. If it doesn't exist, it will be created.
     *
     * @return Cookies The Cookies instance.
     */
    public function cookies(): Cookies
    {
        return $this->cookies ??= new Cookies();
    }


    public function dbManager(): DatabaseManager
    {
        return $this->dbManager ??= new DatabaseManager();
    }

    // --- Critical Services ---

    /**
     * Get the Session instance.
     */
    public function session(): ?Session
    {
        return $this->session;
    }

    /**
     * Get the current resolved route information.
     */
    public function route(): ?ResolvedRoute
    {
        return $this->route;
    }

    /**
     * Set the current resolved route information.
     *
     * @param ResolvedRoute $route The resolved route to set in the context.
     */
    public function setRoute(ResolvedRoute $route): void
    {
        $this->route = $route;
    }

    // --- Service Container (optional) ---

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (class_exists($id)) {
            return $this->services[$id] = $this->build($id);
        }

        throw new RuntimeException("Service not found: $id");
    }

    public function tryGet(string $id): ?object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (class_exists($id)) {
            return $this->services[$id] = $this->build($id);
        }

        return null;
    }

    public function getOrNull(string $id): ?object
    {
        return $this->services[$id] ?? null;
    }

    protected function build(string $class): object
    {
        $ref = new \ReflectionClass($class);

        // No constructor -> simple instantiation
        if (!$ref->getConstructor()) {
            return new $class();
        }

        $args = [];

        foreach ($ref->getConstructor()->getParameters() as $param) {

            $typeObj = $param->getType();
            $types = [];

            // Extract all possible types (Named, Union, Intersection)
            if ($typeObj instanceof \ReflectionNamedType) {
                $types[] = $typeObj->getName();
            } elseif ($typeObj instanceof \ReflectionUnionType) {
                foreach ($typeObj->getTypes() as $t) {
                    if ($t instanceof \ReflectionNamedType) {
                        $types[] = $t->getName();
                    }
                }
            } else {
                throw new RuntimeException(
                    "Unsupported parameter type for \${$param->getName()} in $class constructor"
                );
            }

            // Try to resolve via DI (AppContext)
            foreach ($types as $t) {

                // If service is registered
                if ($this->has($t)) {
                    $args[] = $this->get($t);
                    continue 2; // next parameter
                }

                // If class exists -> auto-wire
                if (class_exists($t)) {
                    $args[] = $this->get($t);
                    continue 2;
                }

                // Built-in types (int, string, etc.) are not supported here
            }

            // Default value
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            // Nullable
            if ($param->allowsNull()) {
                $args[] = null;
                continue;
            }

            throw new Exception(
                "Cannot resolve constructor parameter \${$param->getName()} for $class"
            );
        }

        return new $class(...$args);
    }
}

/**
 * Class ResolvedRoute
 *
 * Represents the fully resolved route and execution context used by the
 * dispatcher to invoke the matched controller and action.
 *
 * This includes:
 * - the effective namespace (after applying route group namespaces)
 * - the resolved controller class
 * - the resolved action method name
 * - the resolved action method parameters
 * - route variables extracted from the URL
 * - route middleware groups
 * - route overrides (e.g. controller/action)
 */
class ResolvedRoute
{
    public function __construct(
        public ?string $namespace,
        public string $controller,
        public string $action,
        public array $params,
        public array $vars,
        public array $groups,
        public array $override
    ) {
    }
}
