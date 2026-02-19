# 🧩 Merlin\Mvc\Model

## 🔐 Properties

- `protected 🎲 mixed $__state__`
- `protected static 📦 array $excludedPropertiesCache`
- `protected static 📦 array $defaultReadRoles`
- `protected static 📦 array $defaultWriteRoles`

## 🚀 Public methods

### `source()`

`public function source() : string`

Return the table or view name for this model. By default, it converts the class name from CamelCase to snake_case.

Override this method if you want to specify a custom source.

**➡️ Return value**

- Type: `string`

### `schema()`

`public function schema() : string|null`

Return the database schema for this model, if applicable. By default, it returns null.

Override this method if you want to specify a schema (e.g. for PostgreSQL).

**➡️ Return value**

- Type: `string|null`

### `idFields()`

`public function idFields() : array`

Return the name of the primary key field(s) for this model. By default, it returns ['id'].

Override this method if your model has a different primary key or composite keys.

**➡️ Return value**

- Type: `array`
- Description: List of primary key field names

### `query()`

`public static function query(string|null $alias = null) : Merlin\Db\Query`

Start a new query builder for this model. By default, it creates a Query with the model's source as the table.

You can also use selectBuilder(), insertBuilder(), updateBuilder(), and deleteBuilder() for more specific builders.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$alias` | `string\|null` | `null` | Optional alias for the model in the query |

**➡️ Return value**

- Type: `Merlin\Db\Query`

### `create()`

`public static function create(array $values) : static`

Create a new model instance with the given values and save it to the database. Returns the created instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$values` | `📦 array` | `` | Associative array of field values to set on the new model |

**➡️ Return value**

- Type: `static`
- Description: The created model instance

### `forceCreate()`

`public static function forceCreate(array $values) : static`

Force create a new model instance with the given values, bypassing any checks for required fields or IDs. This is useful for seeding or when you want to manually set all fields including IDs. Returns the created instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$values` | `📦 array` | `` | Associative array of field values to set on the new model |

**➡️ Return value**

- Type: `static`
- Description: The created model instance

### `firstOrCreate()`

`public static function firstOrCreate(array $conditions, array $values = []) : static`

Find the first model matching the given conditions or create a new one with the combined conditions and values if none found. This is useful for ensuring a record exists without creating duplicates. Returns the found or created instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `` | Associative array of field conditions to find the model |
| `$values` | `📦 array` | `[]` | Additional values to set on the model if it needs to be created (merged with conditions) |

**➡️ Return value**

- Type: `static`
- Description: The found or created model instance

### `updateOrCreate()`

`public static function updateOrCreate(array $conditions, array $values = []) : static`

Find the first model matching the given conditions or update it with the provided values if found, otherwise create a new one with the combined conditions and values. This is useful for ensuring a record exists and is up to date without creating duplicates. Returns the found, updated, or created instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `` | Associative array of field conditions to find the model |
| `$values` | `📦 array` | `[]` | Values to set on the model if found (updated) or merged with conditions if created |

**➡️ Return value**

- Type: `static`
- Description: The found, updated, or created model instance

### `find()`

`public static function find(mixed $id) : static|null`

Finds a model by its ID(s)

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🎲 mixed` | `` | Single ID value or array of ID values (for composite keys) |

**➡️ Return value**

- Type: `static|null`

### `findOrFail()`

`public static function findOrFail(mixed $id) : static`

Finds a model by its ID(s) or throws an exception if not found

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$id` | `🎲 mixed` | `` | Single ID value or array of ID values (for composite keys) |

**➡️ Return value**

- Type: `static`

**⚠️ Throws**

- \Exception if the model is not found

### `findOne()`

`public static function findOne(array $conditions) : static|null`

Finds the first model matching the given conditions or returns null if none found.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `` | Associative array of field conditions to find the model |

**➡️ Return value**

- Type: `static|null`
- Description: The found model instance or null if not found

### `findAll()`

`public static function findAll(array $conditions = []) : Merlin\Db\ResultSet`

Find all models matching the given conditions. If no conditions are provided, it returns all models. Returns a ResultSet of model instances.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `[]` | Associative array of field conditions to find the models |

**➡️ Return value**

- Type: `Merlin\Db\ResultSet`
- Description: The found model instances as a ResultSet

### `exists()`

`public static function exists(array $conditions) : bool`

Check if any model exists matching the given conditions. Returns true if at least one record matches, false otherwise.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `` | Associative array of field conditions to check for existence |

**➡️ Return value**

- Type: `bool`
- Description: True if a matching model exists, false otherwise

### `count()`

`public static function count(array $conditions = []) : int`

Count the number of models matching the given conditions. Returns the count as an integer.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$conditions` | `📦 array` | `[]` | Associative array of field conditions to count |

**➡️ Return value**

- Type: `int`
- Description: The count of matching models

### `saveState()`

`public function saveState() : static`

Save the current state of the model for change tracking. This method clones the current instance and stores it in the __state__ property. It should be called after loading or saving the model to establish a baseline for detecting changes.

**➡️ Return value**

- Type: `static`

### `loadState()`

`public function loadState() : static`

Load the saved state of the model back into the current instance. This method copies all properties from the __state__ clone back to the current instance, except for any properties that start with '__' which are considered internal and excluded from state tracking. It should be called before saving if you want to revert any unsaved changes back to the last saved state.

**➡️ Return value**

- Type: `static`

### `getState()`

`public function getState() : static|null`

Get the saved state object for this model. This returns the clone of the model that was saved by saveState(), or null if no state has been saved. You can use this to inspect the original values before changes were made.

**➡️ Return value**

- Type: `static|null`
- Description: The saved state object or null if no state saved

### `hasChanged()`

`public function hasChanged() : bool`

Check if any fields have changed since the last saveState() call. This compares the current field values to the saved state and returns true if there are any differences, or false if all values are the same. It ignores any properties that start with '__' as they are considered internal.

**➡️ Return value**

- Type: `bool`
- Description: True if any fields have changed, false otherwise

### `save()`

`public function save() : bool`

Save the model to the database. If the model has all ID fields set, it performs an UPDATE, otherwise it performs an INSERT. Returns true if the save was successful, false if there were no changes to save.

**➡️ Return value**

- Type: `bool`
- Description: True if the model was saved (inserted or updated), false if there were no changes to save

### `insert()`

`public function insert() : bool`

Insert the model as a new record in the database. This method performs an INSERT regardless of whether ID fields are set. Returns true if the insert was successful.

**➡️ Return value**

- Type: `bool`
- Description: True if the model was inserted successfully

### `update()`

`public function update() : bool`

Update the existing record in the database with any changed fields. This method requires that all ID fields are set and will throw an exception if any are missing. Returns true if the update was successful, false if there were no changes to update.

**➡️ Return value**

- Type: `bool`
- Description: True if the model was updated successfully, false if there were no changes to update

### `delete()`

`public function delete() : bool`

Delete the model from the database. This method requires that all ID fields are set and will throw an exception if any are missing. Returns true if the delete was successful.

**➡️ Return value**

- Type: `bool`
- Description: True if the model was deleted successfully

### `setDefaultRole()`

`public static function setDefaultRole(string $role) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `setDefaultReadRole()`

`public static function setDefaultReadRole(string $role) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `setDefaultWriteRole()`

`public static function setDefaultWriteRole(string $role) : void`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$role` | `🔤 string` | `` |  |

**➡️ Return value**

- Type: `void`

### `readConnection()`

`public function readConnection() : Merlin\Db\Database`

**➡️ Return value**

- Type: `Merlin\Db\Database`

### `writeConnection()`

`public function writeConnection() : Merlin\Db\Database`

**➡️ Return value**

- Type: `Merlin\Db\Database`

