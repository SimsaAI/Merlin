# 🧩 Merlin\Db\SqlCase

Fluent builder for CASE expressions

## 🔐 Properties

- `protected 📦 array $whenClauses`
- `protected 🎲 mixed $elseValue`

## 🚀 Public methods

### `when()`

`public function when($condition, $then) : static`

Add WHEN condition THEN result clause

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | `🎲 mixed` | `` | Condition (scalar or Sql instance) |
| `$then` | `🎲 mixed` | `` | Result value (scalar or Sql instance) |

**➡️ Return value**

- Type: `static`

### `else()`

`public function else($value) : static`

Set ELSE default value

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | `🎲 mixed` | `` | Default value (scalar or Sql instance) |

**➡️ Return value**

- Type: `static`

### `end()`

`public function end() : Merlin\Db\Sql`

Finalize and return CASE expression as Sql

**➡️ Return value**

- Type: `Merlin\Db\Sql`

