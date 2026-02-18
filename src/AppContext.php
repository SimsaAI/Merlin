<?php
namespace Merlin;

use Merlin\Db\Database;
use Merlin\Http\Cookies;
use Merlin\Http\Session;
use Merlin\Mvc\ViewEngine;
use Merlin\Http\Request as HttpRequest;

class AppContext
{
    /** @var Database|null Default database connection. Can be used for both read and write if dbRead and dbWrite are not set.
     */
    public ?Database $db = null;

    /** @var Database|null Read-only database connection. Falls back to the default database if not set.
     */
    public ?Database $dbRead = null;

    /** @var Database|null Write-only database connection. Falls back to the default database if not set.
     */
    public ?Database $dbWrite = null;

    /** @var HttpRequest|null The current HTTP request. */
    public ?HttpRequest $request = null;

    /** @var ViewEngine|null The view engine for rendering templates. */
    public ?ViewEngine $view = null;

    /** @var Session|null The session manager. */
    public ?Session $session = null;

    public ?Cookies $cookies = null;

    /** @var RoutingResult|null The current resolved routing information. */
    public ?RoutingResult $route = null;

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
    public function getRequest(): HttpRequest
    {
        return $this->request ??= new HttpRequest();
    }

    /**
     * Get the ViewEngine instance. If it doesn't exist, it will be created.
     *
     * @return ViewEngine The ViewEngine instance.
     */
    public function getView(): ViewEngine
    {
        return $this->view ??= new ViewEngine();
    }

    /**
     * Get the Cookies instance. If it doesn't exist, it will be created.
     *
     * @return Cookies The Cookies instance.
     */
    public function getCookies(): Cookies
    {
        return $this->cookies ??= new Cookies();
    }


    // --- Critical Services ---

    /**
     * Get the default Database instance. If it doesn't exist, an exception will be thrown.
     *
     * @return Database The default Database instance.
     * @throws \RuntimeException If the default Database is not configured.
     */
    public function getDb(): Database
    {
        if ($this->db === null) {
            throw new \RuntimeException("Default DB not configured");
        }
        return $this->db;
    }

    /**
     * Get the read Database instance. If it doesn't exist, it will fall back to the default Database.
     *
     * @return Database The read Database instance.
     * @throws \RuntimeException If neither the read Database nor the default Database is configured.
     */
    public function getReadDb(): Database
    {
        return $this->dbRead ?? $this->getDb();
    }

    /**
     * Get the write Database instance. If it doesn't exist, it will fall back to the default Database.
     *
     * @return Database The write Database instance.
     * @throws \RuntimeException If neither the write Database nor the default Database is configured.
     */
    public function getWriteDb(): Database
    {
        return $this->dbWrite ?? $this->getDb();
    }

    /**
     * Get the Session instance.
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * Get the current routing information.
     */
    public function getRouting(): ?RoutingResult
    {
        return $this->route;
    }
}

class RoutingResult
{
    public function __construct(
        public string $controller,
        public string $action,
        public ?string $namespace,
        public array $vars,
        public array $params,
        public ?array $groups,
        public array $override
    ) {
    }
}
