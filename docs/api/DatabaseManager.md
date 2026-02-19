# 🧩 Merlin\Db\DatabaseManager

Manages multiple database connections (roles) and their factories.

This class allows the definition of multiple database connections (e.g. "default", "analytics", "logging") and retrieval of them by role. The first role defined will be used as the default when requesting the default connection, but it can be changed by calling setDefaultRole(). Each role can be defined with either a Database instance or a factory callable that returns a Database instance. The factory will only be called once per role, and the resulting Database instance will be cached for future use.

## 🔐 Properties

- `protected 📦 array $factories`
- `protected 📦 array $instances`
- `protected string|null $defaultRole`

## 🚀 Public methods

### `set()`

`public function set(string $role, Merlin\Db\Database|callable $factory) : static`

Define a database connection for a specific role.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` | The name of the role (e.g. "default", "analytics") |
| `$factory` | `Merlin\Db\Database\|callable` | `` | A factory callable that returns a Database instance, or a Database instance directly |

**➡️ Return value**

- Type: `static`

### `setDefaultRole()`

`public function setDefaultRole(string $role) : static`

Set the default database role to use when requesting the default connection. By default, the first defined role will be used as the default.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` | The name of the role to set as default |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \RuntimeException If the specified role is not defined

### `has()`

`public function has(string $role) : bool`

Check if a database role is defined.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` | The name of the role to check |

**➡️ Return value**

- Type: `bool`
- Description: True if the role is defined, false otherwise

### `get()`

`public function get(string $role) : Merlin\Db\Database`

Get the Database instance for a specific role.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` | The name of the role to retrieve |

**➡️ Return value**

- Type: `Merlin\Db\Database`
- Description: The Database instance for the specified role

**⚠️ Throws**

- \RuntimeException If the role is not defined or if the factory does not return a Database instance

### `getOrDefault()`

`public function getOrDefault(string $role) : Merlin\Db\Database`

Get the Database instance for a specific role, or the default if the role is not defined.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` | The name of the role to retrieve |

**➡️ Return value**

- Type: `Merlin\Db\Database`
- Description: The Database instance for the specified role, or the default if not defined

**⚠️ Throws**

- \RuntimeException If no default database is configured

### `default()`

`public function default() : Merlin\Db\Database`

Get the default Database instance.

**➡️ Return value**

- Type: `Merlin\Db\Database`
- Description: The default Database instance

**⚠️ Throws**

- \RuntimeException If no default database is configured

