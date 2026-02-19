<?php

namespace Merlin\Db;

use Merlin\AppContext;

/**
 * Build conditions for WHERE, HAVING, ON etc. clauses
 * 
 * Usage examples:
 * 
 *   // Simple condition
 *   $c = Condition::create()->where('id', 123);
 * 
 *   // Qualified identifiers (automatically quoted)
 *   $c = Condition::create()->where('users.status', 'active');
 * 
 *   // Large IN lists (no regex issues)
 *   $c = Condition::create()->inWhere('id', range(1, 10000));
 * 
 *   // JOIN conditions
 *   $joinCond = Condition::create()->where('o.user_id = u.id');
 *   $sb->leftJoin('orders o', $joinCond);
 * 
 *   // Complex conditions
 *   $c = Condition::create()
 *       ->where('u.age', 18, '>=')
 *       ->andWhere('u.status', 'active')
 *       ->groupStart()
 *           ->where('u.role', 'admin')
 *           ->orWhere('u.role', 'moderator')
 *       ->groupEnd();
 */
class Condition
{
	/**
	 * @var Database|null
	 */
	protected ?Database $db;

	/**
	 * @var string
	 */
	protected string $condition = '';

	/**
	 * @var bool
	 */
	protected bool $needOperator = false;

	/**
	 * @var int
	 */
	protected int $paramCounter = 0;

	/**
	 * @var array
	 */
	protected array $autoBindParams = [];

	/**
	 * @var callable|null Callable to resolve model names to table names
	 */
	protected $modelResolver = null;

	/**
	 * @var array Cache of model-to-table mappings
	 */
	protected array $tableCache = [];

	/**
	 * @var array<string,string> Deferred model prefixes (quotedModelDot => model name)
	 */
	protected array $deferredModelPrefixes = [];

	/**
	 * @var string
	 */
	protected ?string $finalCondition = null;

	/**
	 * Create a new Condition builder instance
	 * @param Database|null $db
	 * @return static
	 */
	public static function new(?Database $db = null): static
	{
		return new static($db);
	}

	/**
	 * @param ?Database $db
	 * @throws Exception
	 */
	public function __construct(?Database $db = null)
	{
		$this->db = $db;
	}

	/**
	 * Get database connection, resolving to default if not set
	 * @return Database
	 * @throws Exception
	 */
	protected function getDb(): Database
	{
		return $this->db ?? AppContext::instance()->dbManager()->default();
	}

	/**
	 * Hook for resolving table names in Condition context
	 * @param string $model
	 * @return string
	 */
	protected function resolveTableNameOrDefer(string $model): string
	{
		// If we have a model resolver injected from Query builder, use it
		if ($this->modelResolver !== null) {
			try {
				return ($this->modelResolver)($model);
			} catch (\Exception $e) {
				// Fall back to escaping if resolution fails
			}
		}

		// Defer model-like identifiers while keeping condition text readable.
		if (str_contains($model, '\\') || (isset($model[0]) && ctype_upper($model[0]))) {
			$quoted = $this->quoteIdentifier($model);
			$this->deferredModelPrefixes[$quoted . '.'] = $model;
			return $quoted;
		}

		// Otherwise treat it as alias/plain table name and escape immediately.
		return $this->quoteIdentifier($model);
	}

	/**
	 * Inject model resolver from Query builder
	 * @param callable $resolver Callable that takes model name and returns table name
	 * @return void
	 */
	public function injectModelResolver(callable $resolver): void
	{
		$this->modelResolver = $resolver;
	}

	/**
	 * Resolve deferred model tokens in SQL.
	 * Uses injected resolver when available, otherwise falls back to quoted model name.
	 * @param string $sql
	 * @return string
	 */
	protected function resolveDeferredModels(string $sql): string
	{
		if (empty($this->deferredModelPrefixes)) {
			return $sql;
		}

		$replacements = [];
		foreach ($this->deferredModelPrefixes as $prefix => $model) {
			if ($this->modelResolver !== null) {
				try {
					$replacements[$prefix] = ($this->modelResolver)($model) . '.';
					continue;
				} catch (\Exception $e) {
					// Fall through to quoted fallback below.
				}
			}
			$replacements[$prefix] = $prefix;
		}

		return strtr($sql, $replacements);
	}

	/**
	 * Appends a condition to the current conditions using an AND operator
	 * @param string|Condition $condition
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function where(string|Condition $condition, $value = null, bool $escape = true): static
	{
		return $this->addWhere($condition, ' AND ', $value, $escape);
	}

	/**
	 * Appends a condition to the current conditions using a OR operator
	 * @param string|Condition $condition
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function orWhere(string|Condition $condition, $value = null, bool $escape = true): static
	{
		return $this->addWhere($condition, ' OR ', $value, $escape);
	}

	/**
	 * Appends a condition to the current conditions using an operator
	 * @param string|Condition $condition
	 * @param string $operator
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	private function addWhere(string|Condition $condition, string $operator, $value = null, bool $escape = true): static
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		if ($condition instanceof Condition) {
			// sub conditions
			if ($this instanceof Query) {
				$condition->injectModelResolver(
					fn($model) => $this->getTableName($model)
				);
			}
			$escape = false;
			$condition = $condition->toSql();
		} elseif (\is_array($value)) {
			// phalcon style
			$condition = $this->replacePlaceholders($condition, $value);
		} elseif (isset($value)) {
			if ($value instanceof Query) {
				// sub select
				$escape = false;
				$value = '(' . $value->toSql() . ')';
			} elseif ($value instanceof Condition) {
				// sub conditions - inject model resolver if available
				if ($this instanceof Query) {
					$value->injectModelResolver(
						fn($model) => $this->getTableName($model)
					);
				}
				$escape = false;
				$value = '(' . $value->toSql() . ')';
			}
			// ci style - protect identifier
			$condition = $this->protectIdentifier($condition);
			$condition = $this->appendOperator($condition);
			$condition .= $escape ? $this->escapeValue($value) : $value;
		} else {
			// Plain condition string - parse and protect identifiers
			$condition = $this->protectConditionString($condition);
		}
		$this->condition .= '(';
		$this->condition .= $condition;
		$this->condition .= ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends a BETWEEN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $minimum
	 * @param $maximum
	 * @return $this
	 */
	public function betweenWhere(string $condition, $minimum, $maximum): static
	{
		return $this->addBetweenWhere($condition, ' AND ', ' BETWEEN ', $minimum, $maximum);
	}

	/**
	 * Appends a NOT BETWEEN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $minimum
	 * @param $maximum
	 * @return $this
	 */
	public function notBetweenWhere(string $condition, $minimum, $maximum): static
	{
		return $this->addBetweenWhere($condition, ' AND ', ' NOT BETWEEN ', $minimum, $maximum);
	}

	/**
	 * Appends a BETWEEN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $minimum
	 * @param $maximum
	 * @return $this
	 */
	public function orBetweenWhere(string $condition, $minimum, $maximum): static
	{
		return $this->addBetweenWhere($condition, ' OR ', ' BETWEEN ', $minimum, $maximum);
	}

	/**
	 * Appends a NOT BETWEEN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $minimum
	 * @param $maximum
	 * @return $this
	 */
	public function orNotBetweenWhere(string $condition, $minimum, $maximum): static
	{
		return $this->addBetweenWhere($condition, ' OR ', ' NOT BETWEEN ', $minimum, $maximum);
	}

	/**
	 * Appends a BETWEEN condition to the current conditions
	 * @param string $condition
	 * @param string $operator
	 * @param string $between
	 * @param $minimum
	 * @param $maximum
	 * @return $this
	 */
	private function addBetweenWhere(
		string $condition,
		string $operator,
		string $between,
		$minimum,
		$maximum
	): static {
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		$this->condition .= '(' . $this->protectIdentifier($condition) . $between . $this->escapeValue($minimum) . ' AND ' . $this->escapeValue($maximum) . ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends an IN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function inWhere(string $condition, $values): static
	{
		return $this->addInWhere($condition, ' AND ', 'IN', $values);
	}

	/**
	 * Appends an NOT IN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function notInWhere(string $condition, $values): static
	{
		return $this->addInWhere($condition, ' AND ', 'NOT IN', $values);
	}

	/**
	 * Appends an IN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function orInWhere(string $condition, $values): static
	{
		return $this->addInWhere($condition, ' OR ', 'IN', $values);
	}

	/**
	 * Appends an NOT IN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function orNotInWhere(string $condition, $values): static
	{
		return $this->addInWhere($condition, ' OR ', 'NOT IN', $values);
	}

	/**
	 * Appends an NOT IN condition to the current conditions
	 * @param string $condition
	 * @param string $operator
	 * @param string $in
	 * @param $values
	 * @return $this
	 */
	private function addInWhere(string $condition, string $operator, string $in, $values): static
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		$protectedCondition = $this->protectIdentifier($condition);
		if ($values instanceof Condition) {
			if ($this instanceof Query) {
				$values->injectModelResolver(
					fn($model) => $this->getTableName($model)
				);
			}
			$this->condition .= '(' . $protectedCondition . " $in (" . $values->toSql() . '))';
		} else {
			$this->condition .= '(' . $protectedCondition . " $in (" . $this->escapeValue($values) . '))';
		}
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends an HAVING condition to the current conditions using AND operator
	 * @param string|Sql $condition
	 * @param array|null $values
	 * @return $this
	 */
	public function having(string|Sql $condition, $values = null): static
	{
		return $this->addHaving($condition, ' AND ', 'HAVING', $values);
	}

	/**
	 * Appends an NOT HAVING condition to the current conditions using AND operator
	 * @param string|Sql $condition
	 * @param array|null $values
	 * @return $this
	 */
	public function notHaving(string|Sql $condition, $values = null): static
	{
		return $this->addHaving($condition, ' AND ', 'NOT HAVING', $values);
	}

	/**
	 * Appends an HAVING condition to the current conditions using OR operator
	 * @param string|Sql $condition
	 * @param array|null $values
	 * @return $this
	 */
	public function orHaving(string|Sql $condition, $values = null): static
	{
		return $this->addHaving($condition, ' OR ', 'HAVING', $values);
	}

	/**
	 * @param string|Sql $condition
	 * @param array|null $values
	 * @return $this
	 **/
	public function orNotHaving(string|Sql $condition, $values = null): static
	{
		return $this->addHaving($condition, ' OR ', 'NOT HAVING', $values);
	}

	/**
	 * Appends an HAVING condition to the current conditions
	 * @param string|Sql $condition
	 * @param string $operator
	 * @param string $having
	 * @param array|null $values
	 * @return $this
	 */
	private function addHaving(string|Sql $condition, string $operator, string $having, $values = null): static
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		// If a Sql was provided directly, serialize it and ignore $values
		if ($condition instanceof Sql) {
			$condition = $this->serializeScalar($condition);
		} elseif (\is_array($values)) {
			// phalcon style
			$condition = $this->replacePlaceholders($condition, $values);
		}

		$this->condition .= "($having $condition)";
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends a LIKE condition to the current condition
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function likeWhere(string $identifier, $value, bool $escape = true): static
	{
		$this->addLikeWhere($identifier, $value, $escape, " AND ", " LIKE ");
		return $this;
	}

	/**
	 * Appends a LIKE condition to the current condition using an AND operator
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function andLikeWhere(string $identifier, $value, bool $escape = true): static
	{
		$this->addLikeWhere($identifier, $value, $escape, " AND ", " LIKE ");
		return $this;
	}

	/**
	 * Appends a LIKE condition to the current condition using an OR operator
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function orLikeWhere(string $identifier, $value, bool $escape = true): static
	{
		$this->addLikeWhere($identifier, $value, $escape, " OR ", " LIKE ");
		return $this;
	}

	/**
	 * Appends a NOT LIKE condition to the current condition
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function notLikeWhere(string $identifier, $value, bool $escape = true): static
	{
		$this->addLikeWhere($identifier, $value, $escape, " AND ", " NOT LIKE ");
		return $this;
	}

	/**
	 * Appends a NOT LIKE condition to the current condition using an AND operator
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function andNotLikeWhere(string $identifier, $value, bool $escape = true): static
	{
		$this->addLikeWhere($identifier, $value, $escape, " AND ", " NOT LIKE ");
		return $this;
	}

	/**
	 * Appends a NOT LIKE condition to the current condition using an OR operator
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function orNotLikeWhere(string $identifier, $value, bool $escape = true): static
	{
		return $this->addLikeWhere($identifier, $value, $escape, " OR ", " NOT LIKE ");
	}

	/**
	 * Appends a LIKE condition to the current condition
	 * @param string $identifier
	 * @param $value
	 * @param bool $escape
	 * @param string $operator
	 * @param string $like
	 * @return $this
	 */
	private function addLikeWhere(
		string $identifier,
		$value,
		bool $escape,
		string $operator,
		string $like
	): static {
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		$this->condition .= '(';
		$this->condition .= $this->protectIdentifier($identifier);
		$this->condition .= $like;
		$this->condition .= $escape ? $this->escapeValue($value) : $value;
		$this->condition .= ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Starts a new group by adding an opening parenthesis to the WHERE clause of the query.
	 * @return $this
	 */
	public function groupStart(): static
	{
		if ($this->needOperator) {
			$this->condition .= ' AND ';
			$this->needOperator = false;
		}
		$this->condition .= '(';
		return $this;
	}

	/**
	 * Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR’.
	 * @return $this
	 */
	public function orGroupStart(): static
	{
		if ($this->needOperator) {
			$this->condition .= ' OR ';
			$this->needOperator = false;
		}
		$this->condition .= '(';
		return $this;
	}

	/**
	 * Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘NOT’.
	 * @return $this
	 */
	public function notGroupStart(): static
	{
		if ($this->needOperator) {
			$this->condition .= ' AND ';
			$this->needOperator = false;
		}
		$this->condition .= 'NOT (';
		return $this;
	}

	/**
	 * Starts a new group by adding an opening parenthesis to the WHERE clause of the query, prefixing it with ‘OR NOT’.
	 * @return $this
	 */
	public function orNotGroupStart(): static
	{
		if ($this->needOperator) {
			$this->condition .= ' OR ';
			$this->needOperator = false;
		}
		$this->condition .= 'NOT (';
		return $this;
	}

	/**
	 * Ends the current group by adding an closing parenthesis to the WHERE clause of the query.
	 * @return $this
	 */
	public function groupEnd(): static
	{
		$this->condition .= ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * No operator function. Useful to build flexible chains
	 * @return $this
	 */
	public function noop(): static
	{
		return $this;
	}

	/**
	 * Append CI style operator to condition string
	 * @param string $condition
	 * @return string
	 */
	private function appendOperator(string $condition): string
	{
		$condition = rtrim($condition);
		$index = strlen($condition) - 1;
		if ($index >= 0) {
			switch ($condition[$index]) {
				case '=':
				case '<':
				case '>':
					break;
				default:
					$condition .= ' =';
					break;
			}
			$condition .= ' ';
		}
		return $condition;
	}

	/**
	 * Replace placeholders with escaped values
	 * Supports both positional (?) and named (:name) placeholders
	 * @param string $condition
	 * @param array|null $bindParams
	 * @return string
	 */
	protected function replacePlaceholders(string $condition, ?array $bindParams): string
	{
		if (!empty($bindParams)) {
			foreach ($bindParams as $key => $value) {
				$escapedValue = $this->serializeScalar($value);

				if (is_int($key)) {
					$condition = str_replace('?', $escapedValue, $condition);
				} else {
					$condition = str_replace(':' . $key, $escapedValue, $condition);
				}
			}
		}
		return $condition;
	}

	/**
	 * Serialize a value to SQL (handles Sql instances)
	 * @param mixed $value
	 * @param bool $param Whether to serialize as a bind parameter
	 * @return string
	 */
	protected function serializeScalar($value, bool $param = false): string
	{
		// Sql instances serialize themselves
		if ($value instanceof Sql) {

			$result = $value->toSql(
				$this->getDb()->getDriver(),
				fn($v, $p = false) => $this->serializeScalar($v, $p),
				fn($identifier) => $this->protectIdentifier($identifier, self::PI_COLUMN)
			);

			// merge bind parameters from node into current builder
			$nodeBindParams = $value->getBindParams();
			if (!empty($nodeBindParams)) {
				$result = $this->replacePlaceholders(
					$result,
					$nodeBindParams
				);
			}

			return $result;
		}

		if ($param) {
			// Param mode: create bind parameter
			$id = spl_object_id($this); // ensure unique ID for objects
			$name = "__p{$id}_" . (++$this->paramCounter);
			$this->autoBindParams[$name] = $value;
			return ':' . $name;
		} else {
			// Literal mode: escape to SQL literal
			return $this->escapeValue($value);
		}

	}

	/**
	 * Escape a value
	 * @param mixed $value
	 * @return string
	 */
	protected function escapeValue($value)
	{
		// PostgreSQL Array Support
		if (is_array($value)) {
			// special array with value + escape flag
			$isSpecialArray =
				count($value) === 2 &&
				isset($value['value']) &&
				isset($value['escape']);

			if ($isSpecialArray) {
				return $value['escape']
					? $this->escapeValue($value['value'])
					: (string) $value['value'];
			}

			$result = "";
			$sep = "";
			foreach ($value as $v) {
				$result .= $sep;
				$sep = ",";
				$result .= $this->escapeValue($v);
			}
			return $result;
		}

		// scalars
		if ($value === null) {
			return 'NULL';
		}

		if (is_int($value) || is_float($value)) {
			return $value;
		}

		if (is_bool($value)) {
			if ($this->getDb()->getDriver() === 'pgsql') {
				return $value ? 'TRUE' : 'FALSE';
			}
			return $value ? '1' : '0';
		}

		if ($value instanceof \DateTimeInterface) {
			return $this->getDb()->quote($value->format('Y-m-d H:i:s'));
		}

		if ($value instanceof \BackedEnum) {
			return $this->escapeValue($value->value);
		}

		if ($value instanceof \UnitEnum) {
			return $this->escapeValue($value->name);
		}

		if ($value instanceof Query) {
			// sub select
			// merge auto-bind parameters from sub select into current builder
			$this->autoBindParams = array_merge($value->autoBindParams, $this->autoBindParams);
			if ($this instanceof Query) {
				$this->bindParams = array_merge($value->bindParams, $this->bindParams);
			}
			return '(' . $value->toSql() . ')';
		}

		if ($value instanceof Condition) {
			// sub conditions - inject model resolver if available
			if ($this instanceof Query) {
				$value->injectModelResolver(function ($model) {
					return $this->getTableName($model);
				});
			}
			// merge auto-bind parameters from sub conditions into current builder
			$this->autoBindParams = array_merge($value->autoBindParams, $this->autoBindParams);
			return '(' . $value->toSql() . ')';
		}

		return $this->getDb()->quote((string) $value);
	}

	/**
	 * Quote column and table name
	 * @param string $item
	 * @return string
	 */
	protected function quoteIdentifier(string $item): string
	{
		if (empty($item) || $item == '*') {
			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		if (ctype_digit($item) || $item[0] === "'" || $item[0] === '"' || str_contains($item, '(')) {
			return $item;
		}
		return $this->getDb()->quoteIdentifier($item);
	}

	/**
	 * Get table name from cache or resolve it
	 * @param string $model Model or table reference
	 * @return string Resolved table name or marker for deferred resolution
	 */
	protected function getTableName(string $model): string
	{
		if (isset($this->tableCache[$model])) {
			return $this->tableCache[$model];
		}
		// Hook for subclasses to handle resolution
		return $this->resolveTableNameOrDefer($model);
	}

	protected const PI_DEFAULT = 0;
	protected const PI_COLUMN = 1;
	protected const PI_TABLE = 2;

	/**
	 * Protect identifier (field or table name)
	 * @param string $item
	 * @param int $type
	 * @param string|null $alias
	 * @return string
	 */
	protected function protectIdentifier(
		string $item,
		int $type = self::PI_DEFAULT,
		?string $alias = null
	): string {
		$item = preg_replace('/\s+/', ' ', $item);

		// Extract alias from "item AS alias" or "item alias"
		// Orderby/Groupby can have ASC/DESC as "alias"
		if (!isset($alias)) {
			if ($offset = strripos($item, ' AS ')) {
				$alias = substr($item, $offset + 4);
				$item = substr($item, 0, $offset);
			} elseif ($offset = strrpos($item, ' ')) {
				$alias = substr($item, $offset + 1);
				$item = substr($item, 0, $offset);
			}
		}

		// Only process items without functions, quotes, or parentheses
		if (strcspn($item, "()'") === strlen($item)) {
			$item = trim($item);

			if ($type === self::PI_TABLE) {
				// Table mode: use full table name resolution with alias support
				return $this->getFullTableName($item, $alias);
			}

			// Column mode: handle qualified identifiers (table.column)
			$index = strpos($item, '.');
			if ($index > 0) {
				$table = $this->getTableName(substr($item, 0, $index));
				$item = $table . '.' . $this->quoteIdentifier(substr($item, $index + 1));
			} else {
				$item = $this->quoteIdentifier($item);
			}
		}

		// Add alias
		if (!empty($alias)) {
			if ($type === self::PI_TABLE || $type === self::PI_COLUMN) {
				$item .= ' AS ' . $this->quoteIdentifier($alias);
			} else {
				$item .= ' ' . $alias;
			}
		}

		return $item;
	}

	/**
	 * Parse and protect identifiers in a simple condition string
	 * Handles basic operators: =, !=, <, >, <=, >=, <>, IS NULL, IS NOT NULL
	 * @param string $condition
	 * @return string
	 */
	protected function protectConditionString(string $condition): string
	{
		// List of operators to search for (order matters - longer first)
		$operators = [
			'IS NOT NULL',
			'IS NULL',
			'!=',
			'<=',
			'>=',
			'<>',
			'=',
			'<',
			'>'
		];

		foreach ($operators as $op) {
			$pos = stripos($condition, $op);
			if ($pos !== false) {
				$lhs = trim(substr($condition, 0, $pos));
				$rhs = trim(substr($condition, $pos + strlen($op)));

				// Protect LHS (always an identifier)
				$lhs = $this->protectIdentifier($lhs);

				// Protect RHS if it looks like an identifier (not a literal, not empty for IS NULL)
				if (
					!empty($rhs) &&
					!is_numeric($rhs) &&
					$rhs[0] !== "'" &&
					$rhs[0] !== '"' &&
					$rhs[0] !== '(' &&
					stripos($rhs, ':') === false
				) {
					$rhs = $this->protectIdentifier($rhs);
				}

				return $lhs . ' ' . $op . (!empty($rhs) ? ' ' . $rhs : '');
			}
		}

		// No operator found - return as-is
		return $condition;
	}

	/**
	 * Get full table name with model resolution (only available in Query builder context)
	 * @param string $modelName
	 * @param string|null $alias
	 * @return string
	 */
	protected function getFullTableName(string $modelName, ?string $alias): string
	{
		throw new \BadFunctionCallException('getFullTableName() is only available in Query builder context');
	}

	/**
	 * Replace placeholders in the condition with actual values
	 * @param array $bindParams
	 * @return $this
	 */
	public function bind(array $bindParams): static
	{
		if (\count($this->autoBindParams) > $this->paramCounter) {
			// Remove old auto-bind parameters that were not used in the current condition
			array_splice($this->autoBindParams, 0, $this->paramCounter);
		}
		$bindParams = array_merge($this->autoBindParams, $bindParams);
		$this->finalCondition = $this->replacePlaceholders(
			$this->condition,
			$bindParams
		);
		return $this;
	}

	/**
	 * Get the condition
	 * @return string
	 */
	public function toSql(): string
	{
		$sql = $this->finalCondition ?? $this->condition;
		return $this->resolveDeferredModels($sql);
	}
}
