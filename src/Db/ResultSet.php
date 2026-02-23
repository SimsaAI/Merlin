<?php

namespace Merlin\Db;

use Merlin\Mvc\Model;
use PDO;

/**
 * @template TModel of Model
 */
class ResultSet implements \Iterator, \Countable
{
	protected Database $db;
	protected \PDOStatement $statement;
	protected ?string $sqlStatement;
	protected ?array $boundParams;
	/** @var class-string<TModel> */
	protected ?string $modelClass;
	protected int $fetchMode;
	protected mixed $firstObject = null;

	// Iterator state
	protected mixed $currentRow = null;
	protected int $position = 0;
	protected bool $initialized = false;

	/**
	 * Create a new ResultSet wrapping a PDO statement result.
	 *
	 * @param Database        $connection   Database connection used to execute the query.
	 * @param \PDOStatement   $statement    The executed PDO statement.
	 * @param string|null     $sqlStatement The original SQL string (used by reexecute()).
	 * @param array|null      $boundParams  Bound parameters (used by reexecute()).
	 * @param Model|null      $model        Optional model instance used for hydration (sets the fetch class).
	 */
	public function __construct(
		Database $connection,
		\PDOStatement $statement,
		?string $sqlStatement = null,
		?array $boundParams = null,
		?Model $model = null
	) {
		$this->db = $connection;
		$this->statement = $statement;
		$this->sqlStatement = $sqlStatement;
		$this->boundParams = $boundParams;
		$this->modelClass = $model ? \get_class($model) : null;

		$this->fetchMode = $connection->getInternalConnection()
			->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
	}

	/**
	 * Fetch next row as object or array depending on fetch mode.
	 */
	public function fetch(): object|array|false
	{
		$this->position++;
		return $this->statement->fetch($this->fetchMode);
	}

	/**
	 * Fetch next row as associative array.
	 * @return array|false
	 */
	public function fetchArray(): array|false
	{
		$this->position++;
		return $this->statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Fetch next row as object.
	 * @return object|false
	 */
	public function fetchObject(): object|false
	{
		$this->position++;
		return $this->statement->fetch(PDO::FETCH_OBJ);
	}

	/**
	 * Fetch next row as a single column value.
	 * @param int $column
	 */
	public function fetchColumn(int $column = 0): mixed
	{
		$this->position++;
		return $this->statement->fetchColumn($column);
	}

	/**
	 * Fetch all values from a single column.
	 * @param int $column
	 * @return array
	 */
	public function fetchAllColumns(int $column = 0): array
	{
		$result = $this->statement->fetchAll(PDO::FETCH_COLUMN, $column);
		$this->position += \count($result);
		return $result;
	}

	/**
	 * Fetch all rows as objects or arrays depending on fetch mode.
	 * @param int $fetchMode Override fetch mode for this call (optional)
	 * @param int $columnIndex Column index for PDO::FETCH_COLUMN mode (optional)
	 */
	public function fetchAll(int $fetchMode = 0, int $columnIndex = 0): array
	{
		$result = $this->statement->fetchAll($fetchMode ?: $this->fetchMode, $columnIndex);
		$this->position += \count($result);
		return $result;
	}

	/**
	 * Set the default fetch mode for this result set.
	 * @param int $fetchMode One of the PDO::FETCH_* constants
	 */
	public function setFetchMode(int $fetchMode): void
	{
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Return all rows as associative arrays.
	 * @return array
	 */
	public function allArrays(): array
	{
		$result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
		$this->position += \count($result);
		return $result;
	}

	/**
	 * Return all rows as objects.
	 * @return array
	 */
	public function allObjects(): array
	{
		$result = $this->statement->fetchAll(PDO::FETCH_OBJ);
		$this->position += \count($result);
		return $result;
	}

	/**
	 * Get the next model from the result set, or false if there are no more models. This method will attempt to hydrate a model if a model class was provided when the ResultSet was created. If no model class was provided, it will return false.
	 * @return TModel|null
	 */
	public function nextModel(): ?Model
	{
		// If no model is available, model hydration is impossible
		if (!$this->modelClass) {
			return null;
		}

		// Hydrate via PDO
		$this->statement->setFetchMode(
			PDO::FETCH_CLASS,
			$this->modelClass
		);

		$model = $this->statement->fetch();

		if ($model === false) {
			return null;
		}

		// Save state for ORM
		if ($model instanceof Model) {
			$model->saveState();
		}

		// Cache first model if not cached yet
		if ($this->firstObject === null) {
			$this->firstObject = $model;
		}

		$this->position++;
		return $model;
	}

	/**
	 * Get first model or object from result set.
	 * @return TModel|null
	 */
	public function firstModel(): ?Model
	{
		// If already cached, return cached model
		if ($this->firstObject !== null) {
			return ($this->firstObject instanceof Model) ? $this->firstObject : null;
		}
		// If no model available, we cannot hydrate
		if (!$this->modelClass) {
			return null;
		}
		// If cursor already moved, we cannot reliably return the first model
		if ($this->position > 0) {
			return null;
		}
		// Fetch first model
		return $this->nextModel();
	}

	/**
	 * Get all remaining models or objects from result set.
	 * @return array<int, TModel>
	 */
	/**
	 * Get all remaining rows hydrated as model instances.
	 *
	 * Calls {@see nextModel()} repeatedly until the result set is exhausted.
	 * Returns an empty array when no model class was provided at construction.
	 *
	 * @return array<int, TModel>
	 */
	public function allModels(): array
	{
		// If no model available, we cannot hydrate
		if (!$this->modelClass) {
			return [];
		}
		// Fetch all models until no more are available
		$models = [];
		while ($model = $this->nextModel()) {
			$models[] = $model;
		}
		return $models;
	}

	/**
	 * Return the SQL statement that was executed to produce this result set, if available.
	 * @return string|null
	 */
	public function getSql(): ?string
	{
		return $this->sqlStatement;
	}

	/**
	 * Return the variables that were bound to the SQL statement, if available.
	 * @return array|null
	 */
	public function getBindings(): ?array
	{
		return $this->boundParams;
	}

	/**
	 * Execute the query again to repopulate the result set.
	 * @return void
	 */
	public function reexecute(): void
	{
		$stmt = $this->db->query(
			$this->sqlStatement,
			$this->boundParams
		);
		$this->statement = $stmt;
		$this->currentRow = null;
		$this->position = 0;
		$this->initialized = false;
		$this->firstObject = null;
	}

	// Iterator methods

	/** Rewind is a no-op: the result set cursor is forward-only. */
	public function rewind(): void
	{
		// The iterator is forwards-only, so we cannot rewind
	}

	/** Return the current row (fetched lazily on first access). */
	public function current(): mixed
	{
		if (!$this->initialized) {
			$this->currentRow = $this->fetch();
			$this->initialized = true;
		}
		return $this->currentRow;
	}

	/** Return the zero-based position of the current row within this traversal. */
	public function key(): int
	{
		return $this->position;
	}

	/** Advance to the next row. */
	public function next(): void
	{
		$this->currentRow = $this->fetch();
		$this->position++;
	}

	/** Return true while the current row is not false/null (i.e., while rows remain). */
	public function valid(): bool
	{
		return $this->currentRow !== false && $this->currentRow !== null;
	}

	/**
	 * Return the number of rows affected/returned by the underlying statement.
	 * @return int Row count as reported by PDOStatement::rowCount().
	 */
	public function count(): int
	{
		return $this->statement->rowCount();
	}

}