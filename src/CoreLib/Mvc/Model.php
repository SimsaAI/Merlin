<?php

namespace CoreLib\Mvc;

use CoreLib\Db\BaseConditionBuilder;
use CoreLib\Db\Exception;
use CoreLib\Db\PdoDriver;
use CoreLib\Db\DeleteBuilder;
use CoreLib\Db\InsertBuilder;
use CoreLib\Db\SelectBuilder;
use CoreLib\Db\UpdateBuilder;
use ReflectionClass;
use ReflectionException;

/**
 * Model class
 */
abstract class Model
{

	/**
	 * Select using builder
	 * @param string|null $alias - Model alias
	 * @param PdoDriver|null $db
	 * @return SelectBuilder
	 * @throws Exception
	 */
	public static function selectBuilder(?string $alias = null, ?PdoDriver $db = null): SelectBuilder
	{
		return (new SelectBuilder($db))->from(static::class, $alias);
	}

	/**
	 * Insert using builder
	 * @param PdoDriver|null $db
	 * @return InsertBuilder
	 * @throws Exception
	 */
	public static function insertBuilder(?PdoDriver $db = null): InsertBuilder
	{
		return (new InsertBuilder($db))->insert(static::class);
	}

	/**
	 * Replace using builder
	 * @param PdoDriver|null $db
	 * @return InsertBuilder
	 * @throws Exception
	 */
	public static function replaceBuilder(?PdoDriver $db = null): InsertBuilder
	{
		return (new InsertBuilder($db))->replace(static::class);
	}

	/**
	 * Update using builder
	 * @param string|null $alias
	 * @param PdoDriver|null $db
	 * @return UpdateBuilder
	 * @throws Exception
	 */
	public static function updateBuilder(?string $alias = null, ?PdoDriver $db = null): UpdateBuilder
	{
		return (new UpdateBuilder($db))->update(static::class, $alias);
	}

	/**
	 * Delete using builder
	 * @param PdoDriver|null $db
	 * @return DeleteBuilder
	 * @throws Exception
	 */
	public static function deleteBuilder(?PdoDriver $db = null): DeleteBuilder
	{
		return (new DeleteBuilder($db))->from(static::class);
	}

	/**
	 * Get schema name
	 * @return string
	 */
	public function schema(): ?string
	{
		return null;
	}

	/**
	 * Get source name
	 * @return string
	 */
	public function source(): string
	{
		return \get_called_class();
	}

	/**
	 * Get id field
	 * @return string
	 */
	public function idField(): string
	{
		return "id";
	}

	/**
	 * Gets the connection used to read data for the model
	 * @return PdoDriver
	 * @throws Exception
	 */
	public function readConnection(): PdoDriver
	{
		return PdoDriver::defaultInstance();
	}

	/**
	 * Gets the connection used to write data to the model
	 * @return PdoDriver
	 * @throws Exception
	 */
	public function writeConnection(): PdoDriver
	{
		return PdoDriver::defaultInstance();
	}

	/**
	 * @var $this
	 */
	protected $__state__;

	/**
	 * Save model state
	 * @return $this
	 */
	public function saveState(): static
	{
		$this->__state__ = clone $this;
		return $this;
	}

	/**
	 * Load model state
	 * @return $this
	 */
	public function loadState(): static
	{
		$state = $this->__state__ ?? null;
		if (isset($state)) {
			foreach ($state as $field => $value) {
				$this->$field = $value;
			}
		}
		return $this;
	}

	/**
	 * Load model state
	 * @return $this
	 */
	public function getState(): static
	{
		return $this->__state__;
	}

	/**
	 * @param array $values
	 * @param ?bool $updateState
	 */
	private function updateState(array $values, ?bool $updateState): void
	{
		if ($updateState !== false) {
			if (!isset($this->__state__)) {
				if ($updateState) {
					$this->__state__ = clone $this;
				}
			} else {
				foreach ($values as $key => $value) {
					$this->__state__->$key = $value;
				}
			}
		}
	}

	/**
	 * Select using builder
	 * @param string|null $alias - Model alias
	 * @param PdoDriver|null $db
	 * @return SelectBuilder
	 * @throws Exception
	 */
	public static function query(?string $alias = null, ?PdoDriver $db = null): SelectBuilder
	{
		return (new SelectBuilder($db))
			->from(\get_called_class(), $alias)
			->wantSaveState(true);
	}

	private static function __getExcludedProperties(): array
	{
		$className = \get_called_class();
		$excludedProperties = [];
		/** @noinspection PhpUnhandledExceptionInspection */
		$reflect = new ReflectionClass($className);
		foreach ($reflect->getProperties() as $property) {
			//echo $property->class, ' => ', $property->name, "<br>\n";
			if (substr_compare($property->name, "_", 0, 1) === 0) {
				$excludedProperties[$property->name] = $property->name;
			}
		}
		return $excludedProperties;
	}

	private function __getChangedValues(): array
	{
		$excludedProperties = self::__getExcludedProperties();
		$values = array_diff_key(get_object_vars($this), $excludedProperties);
		if (isset($this->__state__)) {
			$stateValues = array_diff_key(get_object_vars($this->__state__), $excludedProperties);
			$values = array_diff_assoc($values, $stateValues);
		}
		return $values;
	}

	public function hasChanged(): bool
	{
		return !empty($this->__getChangedValues());
	}

	/**
	 * Update model in database using custom builder class
	 * @param ?bool $updateState (null) update state if exists, (false) do not update state, (true) update state always
	 * @return bool
	 * @throws Exception
	 */
	public function update(?bool $updateState = null): bool
	{
		$values = $this->__getChangedValues();
		if (empty($values)) {
			// nothing to do
			return false;
		}
		$idField = $this->idField();
		$idValue = $this->{$idField} ?? null;
		if (!isset($idValue)) {
			$className = \get_called_class();
			throw new Exception("ID Field $className->{'$idField'} not set");
		}
		unset($values[$idField]);
		$result = self::updateBuilder(null, $this->writeConnection())
			->values($values)
			->where($idField, $idValue)
			->execute(true);
		$this->updateState($values, $updateState);
		return $result;
	}

	/**
	 * Create model in database using custom builder class
	 * @param ?bool $updateState (null) update state if exists, (false) do not update state, (true) update state always
	 * @return bool
	 * @throws Exception
	 */
	public function create(?bool $updateState = true): bool
	{
		$excludedProperties = self::__getExcludedProperties();
		$values = array_diff_key(get_object_vars($this), $excludedProperties);
		$idField = $this->idField();
		// remove id field if value is null
		if (!isset($values[$idField])) {
			unset($values[$idField]);
		}
		$db = $this->writeConnection();
		self::insertBuilder($db)->values($values)->execute(true);
		// set id field to auto increment value
		if (!isset($values[$idField])) {
			$this->{$idField} = $db->getInternalHandler()->lastInsertId();
		}
		$this->updateState($values, $updateState);
		return true;
	}

	/**
	 * Save (update or create) model in database using custom builder class
	 * @param ?bool $updateState (null) update state if exists, (false) do not update state, (true) update state always
	 * @return bool
	 * @throws Exception
	 */
	public function save(?bool $updateState = null): bool
	{
		$values = $this->__getChangedValues();
		if (empty($values)) {
			// nothing to do
			return false;
		}
		$idField = $this->idField();
		$db = $this->writeConnection();
		if (isset($this->__state__)) {
			$idValue = $this->{$idField} ?? null;
			if (!isset($idValue)) {
				// Todo create new model if id field is empty?
				$className = get_called_class();
				throw new Exception("ID Field $className->{'$idField'} not set");
			}
			unset($values[$idField]);
			self::updateBuilder(null, $db)
				->values($values)
				->where($idField, $idValue)
				->execute(true);
		} else {
			// remove id field if value is null
			if (!isset($values[$idField])) {
				unset($values[$idField]);
			}
			self::insertBuilder($db)
				->values($values)
				->upsert($values)
				->execute(true);
			// set id field to auto increment value
			if (!isset($values[$idField])) {
				$this->{$idField} = $db->getInternalHandler()->lastInsertId();
			}
		}
		$this->updateState($values, $updateState);
		return true;
	}

	/**
	 * Delete model from database using custom builder class
	 * @return bool
	 * @throws Exception
	 */
	public function delete(): bool
	{
		$idField = $this->idField();
		$idValue = $this->{$idField} ?? null;
		if (!isset($idValue)) {
			$className = get_called_class();
			throw new Exception("ID Field $className->{'$idField'} not set");
		}
		$db = $this->writeConnection();
		self::deleteBuilder($db)
			->where($idField, $idValue)
			->execute(true);
		return true;
	}
}
