# 🧩 Class: SqlCase

**Full name:** [Merlin\Db\SqlCase](../../src/Db/Sql.php)

Fluent builder for CASE expressions

## 🚀 Public methods

### when() · [source](../../src/Db/Sql.php#L486)

`public function when(mixed $condition, mixed $then): static`

Add WHEN condition THEN result clause

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$condition` | mixed | - | Condition (scalar or Sql instance) |
| `$then` | mixed | - | Result value (scalar or Sql instance) |

**➡️ Return value**

- Type: static


---

### else() · [source](../../src/Db/Sql.php#L497)

`public function else(mixed $value): static`

Set ELSE default value

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | mixed | - | Default value (scalar or Sql instance) |

**➡️ Return value**

- Type: static


---

### end() · [source](../../src/Db/Sql.php#L507)

`public function end(): Merlin\Db\Sql`

Finalize and return CASE expression as Sql

**➡️ Return value**

- Type: [Sql](Db_Sql.md)



---

[Back to the Index ⤴](index.md)
