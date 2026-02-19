<?php
namespace Merlin\Db;

use RuntimeException;

/**
 * Manages multiple database connections (roles) and their factories.
 *
 * This class allows the definition of multiple database connections (e.g. "default", "analytics", "logging") and retrieval of them by role. The first role defined will be used as the default when requesting the default connection, but it can be changed by calling setDefaultRole(). Each role can be defined with either a Database instance or a factory callable that returns a Database instance. The factory will only be called once per role, and the resulting Database instance will be cached for future use.
 */
class DatabaseManager
{
    protected array $factories = [];
    protected array $instances = [];

    protected ?string $defaultRole = null;

    /**
     * Define a database connection for a specific role.
     *
     * @param string $role The name of the role (e.g. "default", "analytics")
     * @param callable|Database $factory A factory callable that returns a Database instance, or a Database instance directly
     * @return $this
     */
    public function set(string $role, callable|Database $factory): static
    {
        $this->factories[$role] = $factory;
        if ($factory instanceof Database) {
            $this->instances[$role] = $factory;
        }
        if ($this->defaultRole === null) {
            $this->defaultRole = $role;
        }
        return $this;
    }

    /**
     * Set the default database role to use when requesting the default connection. By default, the first defined role will be used as the default.
     *
     * @param string $role The name of the role to set as default
     * @return $this
     * @throws RuntimeException If the specified role is not defined
     */
    public function setDefaultRole(string $role): static
    {
        if (!isset($this->factories[$role])) {
            throw new RuntimeException("Cannot set default role: role '$role' is not configured");
        }

        $this->defaultRole = $role;
        return $this;
    }

    /**
     * Check if a database role is defined.
     *
     * @param string $role The name of the role to check
     * @return bool True if the role is defined, false otherwise
     */
    public function has(string $role): bool
    {
        return isset($this->factories[$role]);
    }

    /**
     * Get the Database instance for a specific role.
     *
     * @param string $role The name of the role to retrieve
     * @return Database The Database instance for the specified role
     * @throws RuntimeException If the role is not defined or if the factory does not return a Database instance
     */
    public function get(string $role): Database
    {
        if (isset($this->instances[$role])) {
            return $this->instances[$role];
        }

        if (!isset($this->factories[$role])) {
            throw new RuntimeException("Database role not configured: $role");
        }

        $factory = $this->factories[$role];
        if ($factory instanceof Database) {
            return $this->instances[$role] = $factory;
        }

        $db = $factory();
        if (!$db instanceof Database) {
            throw new RuntimeException("Factory for role $role did not return a Database instance");
        }

        return $this->instances[$role] = $db;
    }

    /**
     * Get the Database instance for a specific role, or the default if the role is not defined.
     *
     * @param string $role The name of the role to retrieve
     * @return Database The Database instance for the specified role, or the default if not defined
     * @throws RuntimeException If no default database is configured
     */
    public function getOrDefault(string $role): Database
    {
        if (isset($this->factories[$role])) {
            return $this->get($role);
        }

        return $this->default();
    }

    /**
     * Get the default Database instance.
     *
     * @return Database The default Database instance
     * @throws RuntimeException If no default database is configured
     */
    public function default(): Database
    {
        if ($this->defaultRole === null) {
            throw new RuntimeException("No database configured");
        }
        return $this->get($this->defaultRole);
    }
}
