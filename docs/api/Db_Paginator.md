# 🧩 Paginator

**Full name:** [Merlin\Db\Paginator](../../src/Db/Paginator.php)

Paginator class for paginating database query results.

## 🚀 Public methods

### __construct() · [source](../../src/Db/Paginator.php#L27)

`public function __construct(Merlin\Db\Query $builder, int $page = 1, int $pageSize = 30, bool $reverse = false): mixed`

Create a new Paginator instance.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$builder` | [Query](Db_Query.md) | - | The Query builder instance to paginate. |
| `$page` | int | `1` | The current page number. |
| `$pageSize` | int | `30` | The number of items per page. |
| `$reverse` | bool | `false` | Whether to reverse the order of items. |

**➡️ Return value**

- Type: mixed


---

### getPageSize() · [source](../../src/Db/Paginator.php#L44)

`public function getPageSize(): int`

Get the page size (number of items per page).

**➡️ Return value**

- Type: int
- Description: The page size.


---

### getTotalItems() · [source](../../src/Db/Paginator.php#L54)

`public function getTotalItems(): int`

Get the total number of items across all pages.

**➡️ Return value**

- Type: int
- Description: The total number of items.


---

### getTotalPages() · [source](../../src/Db/Paginator.php#L64)

`public function getTotalPages(): int`

Get the total number of pages.

**➡️ Return value**

- Type: int
- Description: The total number of pages.


---

### getCurrentPage() · [source](../../src/Db/Paginator.php#L74)

`public function getCurrentPage(): int`

Get the current page number.

**➡️ Return value**

- Type: int
- Description: The current page number.


---

### getFirstItemPos() · [source](../../src/Db/Paginator.php#L84)

`public function getFirstItemPos(): int`

Get the position of the first item in the current page (1-based index).

**➡️ Return value**

- Type: int
- Description: The position of the first item in the current page.


---

### getLastItemPos() · [source](../../src/Db/Paginator.php#L94)

`public function getLastItemPos(): int`

Get the position of the last item in the current page (1-based index).

**➡️ Return value**

- Type: int
- Description: The position of the last item in the current page.


---

### execute() · [source](../../src/Db/Paginator.php#L105)

`public function execute(mixed $fetchMode = 0): array`

Execute the paginated query and return the items for the current page.

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$fetchMode` | mixed | `0` | The PDO fetch mode to use (default: \PDO::FETCH_DEFAULT). |

**➡️ Return value**

- Type: array
- Description: The items for the current page.



---

[Back to the Index ⤴](index.md)
