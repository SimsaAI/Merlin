# 🧩 Class: Column

**Full name:** [Azera\Orm\Attribute\Column](../../src/Orm/Attribute/Column.php)

Declares a property as a persistent column.

Everything is optional: an unattributed declared property is still a
column with inferred defaults (name = property name, type from the PHP
type, falling back to 'string'). The attribute exists to override
those defaults.

`type` is inferred from the property's PHP type when omitted (int →
'int', float → 'float', bool → 'bool', array → 'json', DateTime* →
'datetime', anything else → 'string'); pass `type:` explicitly to
override (e.g. 'pgarray' for a native pg array column).

`pk` explicitly marks (or excludes) a primary key: true marks the
column as part of the PK (composite keys = multiple marked columns);
false excludes a column the *_id name convention would wrongly mark.
An idFields() override on the model still wins over these marks.

`cast` controls the column type's registered cast
([`Casts`](Orm_Casting_Casts.md)) with a TRI-STATE semantic (the same
nullable-bool pattern as `pk`):

- null (default): AUTO — the class's STORE decides. Stores declare wire
  formats they own via the `castExclusions` metadata key contributed in
  enrichMetadata() (mongo excludes 'json', 'pgarray', 'datetime' — the
  driver maps PHP arrays and DateTimeInterface to BSON itself). Types
  not excluded are cast as on SQL (int/float/bool coercion + custom casts).
- true: FORCE the cast even where the store excludes it (mongo stores a
  JSON text string / formatted datetime — the DB no longer owns the type).
- false: SUPPRESS the cast even on SQL (raw pass-through BOTH directions;
  the caller owns the stored representation — e.g. a 'json' column managed
  as raw text, or a driver-stringified numeric you do not want coerced).

## 🌍 Public Properties

- `public` string|null `$type` · [source](../../src/Orm/Attribute/Column.php)
- `public` string|null `$name` · [source](../../src/Orm/Attribute/Column.php)
- `public` bool `$nullable` · [source](../../src/Orm/Attribute/Column.php)
- `public` bool `$transient` · [source](../../src/Orm/Attribute/Column.php)
- `public` bool|null `$pk` · [source](../../src/Orm/Attribute/Column.php)
- `public` bool|null `$cast` · [source](../../src/Orm/Attribute/Column.php)

## 🚀 Public methods

### __construct() · [source](../../src/Orm/Attribute/Column.php#L41)

`public function __construct(string|null $type = null, string|null $name = null, bool $nullable = false, bool $transient = false, bool|null $pk = null, bool|null $cast = null): mixed`

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$type` | string\|null | `null` |  |
| `$name` | string\|null | `null` |  |
| `$nullable` | bool | `false` |  |
| `$transient` | bool | `false` |  |
| `$pk` | bool\|null | `null` |  |
| `$cast` | bool\|null | `null` |  |

**➡️ Return value**

- Type: mixed



---

[Back to the Index ⤴](README.md)
