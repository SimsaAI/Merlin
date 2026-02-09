<?php

namespace CoreLib\Db;

use CoreLib\Mvc\Model;

/**
 * Base class for SELECT, INSERT, UPDATE, DELETE builder
 */
abstract class BaseBuilder extends BaseConditionBuilder
{

	/**
	 * @var bool
	 */
	public static bool $UseModels = true;

	/**
	 * @var Model[]
	 */
	protected static array $modelMap = [];

	/**
	 * @var ModelCache
	 */
	protected static ModelCache $modelCache;

	/**
	 * @param string $modelName
	 * @return Model
	 * @throws Exception
	 */
	protected static function _getModel(string $modelName): Model
	{
		if (!isset(self::$modelMap[$modelName])) {
			if (!class_exists($modelName)) {
				throw new Exception('Undefined model "' . $modelName . '"');
			}
			self::$modelMap[$modelName] = new $modelName();
		}
		return self::$modelMap[$modelName];
	}

	/**
	 * @param ModelCache $modelCache
	 */
	public static function setModelCache(ModelCache $modelCache)
	{
		self::$modelCache = $modelCache;
	}

	/**
	 * @var array
	 */
	protected $_tableCache = [];

	/**
	 * @var Model
	 */
	protected $_model;

	/**
	 * @var array
	 */
	protected $_bindParams = [];

	/**
	 * @var string
	 */
	protected $_sqlStatement;

	/**
	 * @var int
	 */
	protected $_limit;

	/**
	 * @var int
	 */
	protected $_offset;

	/**
	 * @var int
	 */
	protected $_rowCount;

	/**
	 * @var bool
	 */
	protected $_isQuery;

	/**
	 * @var array
	 */
	protected $_columns;

	/**
	 * @var array
	 */
	protected $_protectedColumns;

	/**
	 * @var array
	 */
	protected $_join;

	/**
	 * @var array|string
	 */
	protected $_orderBy;

	/**
	 * @var array
	 */
	protected $_values;

	/**
	 * @var bool
	 */
	protected $_saveState;

	/**
	 * @var bool
	 */
	protected $_getDb;

	/**
	 * @return string
	 */
	protected abstract function _buildSqlStatement(): string;

	/**
	 * @param PdoDriver|null $db
	 * @throws Exception
	 */
	public function __construct(?PdoDriver $db = null, $isQuery = false)
	{
		parent::__construct($db);
		$this->_getDb = empty($db);
		$this->_isQuery = $isQuery;
	}

	/**
	 * Escape column and table name
	 * @param string $item
	 * @return mixed
	 */
	protected function _escapeIdentifier($item)
	{
		if (empty($item) || $item == '*') {
			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		if (ctype_digit($item) || $item[0] === "'" || $item[0] === '"' || strpos($item, '(') !== false) {
			return $item;
		}
		return $this->db->quoteIdentifier($item);
	}

	/**
	 * @param string $sModel
	 * @return string
	 * @throws Exception
	 */
	protected function _getTableName($sModel)
	{
		if (!isset($this->_tableCache[$sModel])) {
			throw new Exception('Reference to undefined "' . $sModel . '" table');
		}
		return $this->_tableCache[$sModel];
	}

	/**
	 * @param string $modelName
	 * @param ?string $alias
	 * @return string
	 * @throws Exception
	 */
	protected function _getFullTableName(string $modelName, ?string $alias): string
	{
		if (isset(self::$modelCache)) {
			$cacheItem = self::$modelCache->get($modelName);
			if (!empty($cacheItem)) {
				$table = $this->_escapeIdentifier($cacheItem['source']);
				$schema = $cacheItem['schema'];
			}
		}
		if (empty($table)) {
			if (self::$UseModels) {
				$model = self::_getModel($modelName);
				$table = $this->_escapeIdentifier($model->source() ?: $modelName);
				$schema = $model->schema();
				if ($this->_getDb) {
					$this->db = $this->_isQuery ? $model->readConnection() : $model->writeConnection();
					$this->_getDb = false;
				}
			} else {
				$items = explode('.', $modelName);
				foreach ($items as $index => $item) {
					$items[$index] = $this->_escapeIdentifier($item);
				}
				$table = implode('.', $items);
			}
		}
		if (!empty($alias)) {
			$escapedAlias = $this->_escapeIdentifier($alias);
			$this->_tableCache[$alias] = $escapedAlias;
		} else {
			$this->_tableCache[$modelName] = $table;
		}
		if (!empty($schema)) {
			$table = $this->_escapeIdentifier($schema) . '.' . $table;
		}
		if (!empty($escapedAlias)) {
			$table .= ' AS ' . $escapedAlias;
		}
		return $table;
	}

	/**
	 * Protect identifier
	 * @param string $sItem
	 * @param string $sAlias
	 * @param bool $bIsTable
	 * @param bool $bIsOrderBy
	 * @return string
	 * @throws Exception
	 */
	protected function _protectIdentifier($sItem, $sAlias = null, $bIsTable = false, $bIsOrderBy = false)
	{
		$sItem = preg_replace('/\s+/', ' ', $sItem);

		if (!isset($sAlias)) {
			if ($offset = strripos($sItem, ' AS ')) {
				$sAlias = substr($sItem, $offset + 4);
				$sItem = substr($sItem, 0, $offset);
			} elseif ($offset = strrpos($sItem, ' ')) {
				$sAlias = substr($sItem, $offset + 1);
				$sItem = substr($sItem, 0, $offset);
			}
		}

		if (strcspn($sItem, "()'") === strlen($sItem)) {
			$sItem = trim($sItem);
			if ($bIsTable) {
				return $this->_getFullTableName($sItem, $sAlias);
			}
			$index = strpos($sItem, '.');
			if ($index > 0) {
				$table = $this->_getTableName(substr($sItem, 0, $index));
				//$table = $this->_escapeIdentifier(substr($sItem, 0, $index));
				$sItem = $table . '.' . $this->_escapeIdentifier(substr($sItem, $index + 1));
			} else {
				$sItem = $this->_escapeIdentifier($sItem);
			}
		}

		if (!empty($sAlias)) {
			if ($bIsOrderBy) {
				$sItem .= ' ' . $sAlias;
			} else {
				$sItem .= ' AS ' . $this->_escapeIdentifier($sAlias);
			}
		}

		return $sItem;
	}

	/**
	 * Compile a condition string
	 * @param string $condition
	 * @return string
	 * @throws Exception
	 */
	protected function _compileCondition($condition)
	{
		// Split multiple conditions
		$subConditions = preg_split(
			'/(X?\'.*?[^\\\]\'|".*?[^\\\]"|(^|\s+)AND\s+|(^|\s+)OR\s+)/i',
			$condition,
			-1,
			PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
		);
		//var_dump($subConditions);
		//exit;

		foreach ($subConditions as $index => $subCondition) {

			if (empty($op = $this->_findOperator($subCondition))) {
				continue;
			}
			$pattern = '/^(\(*)(.*)(' . preg_quote($op, '/') . ')\s*(.*(?<!\)))?(\)*)$/i';
			if (!preg_match($pattern, $subCondition, $match)) {
				continue;
			}

			// $matches = array(
			//	0 => '(test <= foo)',	/* the whole thing */
			//	1 => '(',		/* optional */
			//	2 => 'test',		/* the field name */
			//	3 => ' <= ',		/* $op */
			//	4 => 'foo',		/* optional, if $op is e.g. 'IS NULL' */
			//	5 => ')'		/* optional */
			// );

			if (!empty($match[4])) {
				$str = trim($match[4]);
				if (!(empty($str) || is_numeric($str) || strcspn($str, "'\":?") < strlen($str))) {
					$match[4] = $this->_protectIdentifier($str, '');
				}
				//$match[4] = ' ' . $match[4];
			}

			$subConditions[$index] = $match[1] . $this->_protectIdentifier(trim($match[2]), '') . ' ' .
				trim($match[3]) . ' ' . $match[4] . $match[5];
		}

		return implode('', $subConditions);
	}

	/**
	 * Search for sql operator
	 * @param string $string
	 * @return ?string
	 */
	protected function _findOperator(string $string): ?string
	{
		static $regExp;

		if (empty($regExp)) {
			$operators = [
				'\s*(?:<|>|!)?=\s*',        // =, <=, >=, !=
				'\s*<>?\s*',            // <, <>
				'\s*>\s*',            // >
				'\s+IS\s+NULL',            // IS NULL
				'\s+IS\s+NOT\s+NULL',        // IS NOT NULL
				'\s+EXISTS\s*\([^\)]+\)',    // EXISTS(sql)
				'\s+NOT\s+EXISTS\s*\([^\)]+\)',    // NOT EXISTS(sql)
				'\s+BETWEEN\s+\S+\s+AND\s+\S+',    // BETWEEN value AND value
				//'\s+IN\s*\([^\)]+\)',        // IN(list)
				//'\s+NOT\s+IN\s*\([^\)]+\)',    // NOT IN (list)
				'\s+LIKE\s+',        // LIKE 'expr'[ ESCAPE '%s']
				'\s+NOT\s+LIKE\s+'    // NOT LIKE 'expr'[ ESCAPE '%s']
			];
			$regExp = '/' . implode('|', $operators) . '/i';
		}

		return preg_match($regExp, $string, $match) ? $match[0] : null;
	}

	/**
	 * Protect columns in query
	 * @return array
	 * @throws Exception
	 */
	protected function _protectColumns($columns = null)
	{
		if (isset($columns)) {
			$protectedColumns = [];
			foreach ($columns as $index => $column) {
				$protectedColumns[$index] = $this->_protectIdentifier($column);
			}
			return $protectedColumns;
		}
		if (!empty($this->_columns)) {
			$this->_protectedColumns = [];
			foreach ($this->_columns as $iIndex => $sColumn) {
				$this->_protectedColumns[$iIndex] = $this->_protectIdentifier($sColumn);
			}
		}
		return $this->_protectedColumns;
	}

	/**
	 * Get the SQL statement
	 */
	public function getSQLStatement()
	{
		if (empty($this->_sqlStatement)) {
			// Reset auto-generated parameters for fresh build
			// This prevents parameter accumulation if the builder is reused
			$this->_paramCounter = 0;

			$this->_sqlStatement = $this->_buildSqlStatement();
		}
		return $this->_sqlStatement;
	}

	/**
	 * Get bind parameters
	 */
	public function getSQLVariables()
	{
		return $this->_bindParams;
	}

	/**
	 * Serialize a value to SQL (handles SqlNode instances)
	 * @param mixed $value
	 * @param string $mode 'literal' (escaped SQL) or 'param' (bind parameter)
	 * @return string
	 */
	/*
	protected function serializeScalar($value, string $mode = 'literal'): string
	{
		// SqlNode instances serialize themselves
		if ($value instanceof SqlNode) {
			return $value->toSql(
				$this->db->getDriver(),
				fn($v, $m = 'literal') => $this->serializeScalar($v, $m)
			);
		}

		// Literal mode: escape to SQL literal
		if ($mode === 'literal') {
			return $this->escapeValue($value);
		}

		// Param mode: create bind parameter
		if ($mode === 'param') {
			$name = '__p' . (++$this->_paramCounter);
			if (!isset($this->_bindParams)) {
				$this->_bindParams = [];
			}
			$this->_bindParams[$name] = $value;
			return ':' . $name . ':';
		}

		throw new \LogicException("Unknown serialization mode: $mode");
	}
	*/

	/**
	 * Escape a string
	 * @param string $str
	 * @return string
	 */
	public function escapeString($str)
	{
		return $this->db->escapeString($str);
	}

	/**
	 * Sets the columns to be queried
	 * @param string|array $columns
	 * @return $this
	 */
	public function columns($columns)
	{
		if (!empty($columns)) {
			$this->_columns = is_array($columns) ? $columns : explode(',', $columns);
		} else {
			$this->_columns = null;
		}
		return $this;
	}

	/**
	 * Get the queried columns
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * Sets a LIMIT clause, optionally a offset clause
	 * @param int $limit
	 * @param int $offset
	 * @return $this
	 */
	public function limit($limit, $offset = 0)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}

	/**
	 * Returns the current LIMIT clause
	 * @return int
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * Sets an OFFSET clause
	 * @param $offset
	 */
	public function offset($offset)
	{
		$this->_offset = $offset;
	}

	/**
	 * Returns the current OFFSET clause
	 * @return int
	 */
	public function getOffset()
	{
		return $this->_offset;
	}

	/**
	 * @param array|object $mValues
	 * @param bool $bEscape
	 * @return $this
	 */
	public function values($mValues, $bEscape = true)
	{
		$mValues = is_object($mValues)
			? get_object_vars($mValues) : $mValues;

		if ($bEscape) {
			foreach ($mValues as $index => $value) {
				// SqlNode instances are stored as-is, serialized later
				if (!($value instanceof SqlNode)) {
					$mValues[$index] = $this->escapeValue($value);
				}
			}
		}
		$this->_values[] = $mValues;
		return $this;
	}

	/**
	 * @param array $valuesList
	 * @return $this
	 */
	public function setValues($valuesList = [], $bEscape = true)
	{
		if ($bEscape) {
			foreach ($valuesList as $index => $values) {
				foreach ($values as $key => $value) {
					// SqlNode instances are stored as-is, serialized later
					if (!($value instanceof SqlNode)) {
						$valuesList[$index][$key] = $this->escapeValue($value);
					}
				}
			}
		}
		$this->_values = array_values($valuesList);
		return $this;
	}

	/**
	 * Determine if some values has been set
	 * @return bool
	 */
	public function hasValues()
	{
		return !empty($this->_values[0]);
	}

	/**
	 * Set a column value
	 * @param string|array|object $mColumn
	 * @param mixed $mValue
	 * @param bool $bEscape
	 * @return $this
	 */
	public function set($mColumn, $mValue = null, $bEscape = true)
	{
		if (!isset($this->_values[0])) {
			$this->_values[] = [];
		}
		$index = count($this->_values) - 1;
		if (is_array($mColumn) || is_object($mColumn)) {
			foreach ($mColumn as $sKey => $mValue) {
				if ($bEscape && !($mValue instanceof SqlNode)) {
					$mValue = $this->escapeValue($mValue);
				}
				$this->_values[$index][$sKey] = $mValue;
			}
		} else {
			if ($bEscape && !($mValue instanceof SqlNode)) {
				$mValue = $this->escapeValue($mValue);
			}
			$this->_values[$index][$mColumn] = $mValue;
		}
		return $this;
	}

	/**
	 * Adds a join to the query
	 * @param string $model
	 * @param string $conditions
	 * @param string $alias
	 * @param string $type
	 * @return $this
	 * @throws Exception
	 */
	public function join($model, $conditions = null, $alias = null, $type = null)
	{
		$iPos = strrpos($model, ' ');
		if ($iPos !== false) {
			$alias = substr($model, $iPos + 1);
			$model = substr($model, 0, $iPos);
		}
		if (!isset($this->_join)) {
			$this->_join = [];
		}
		$this->_join[] = [
			'table' => $this->_getFullTableName($model, $alias),
			'conditions' => $conditions,
			'type' => $type
		];
		return $this;
	}

	/**
	 * Adds a INNER join to the query
	 * @param string $model
	 * @param string $conditions
	 * @param string $alias
	 * @return $this
	 * @throws Exception
	 */
	public function innerJoin($model, $conditions = null, $alias = null)
	{
		return $this->join($model, $conditions, $alias, 'INNER');
	}

	/**
	 * Adds a LEFT join to the query
	 * @param string $model
	 * @param string $conditions
	 * @param string $alias
	 * @return $this
	 * @throws Exception
	 */
	public function leftJoin($model, $conditions = null, $alias = null)
	{
		return $this->join($model, $conditions, $alias, 'LEFT');
	}

	/**
	 * Adds a RIGHT join to the query
	 * @param string $model
	 * @param string $conditions
	 * @param string $alias
	 * @return $this
	 * @throws Exception
	 */
	public function rightJoin($model, $conditions = null, $alias = null)
	{
		return $this->join($model, $conditions, $alias, 'RIGHT');
	}

	/**
	 * Sets a ORDER BY condition clause
	 * @param string|array $orderBy
	 * @return $this
	 */
	public function orderBy($orderBy)
	{
		$this->_orderBy = $orderBy;
		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getOrderBy()
	{
		return $this->_orderBy;
	}

	/**
	 * @return string
	 */
	public function getWhere()
	{
		return $this->condition;
	}

	/**
	 * Sets the bound parameters in the criteria
	 * Replaces user-defined parameters while preserving auto-generated ones (p1, p2, etc.)
	 * @param array|object $aBindParams
	 * @return $this
	 */
	public function bind($aBindParams)
	{
		$this->_bindParams = is_object($aBindParams) ? get_object_vars($aBindParams) : $aBindParams;

		return $this;
	}

	/**
	 * Executes a statement using the parameters built with the criteria
	 * @param bool $bDumpQuery
	 * @return ResultSet|bool
	 * @throws \CoreLib\Db\Exception
	 */
	public function execute(bool $bDumpQuery = false)
	{
		$sQuery = $this->getSQLStatement();
		if ($bDumpQuery) {
			echo "<p>SQL: ", $sQuery;
			if (!empty($this->_bindParams)) {
				echo "-- Parameters: ", implode(", ", $this->_bindParams);
			}
			echo "</p>\n\n";
		}
		$this->_sqlStatement = null;
		$bindParams = \array_merge(
			$this->_autoBindParams,
			$this->_bindParams
		);
		$oResult = $this->db->query($sQuery, $bindParams);
		if (empty($oResult)) {
			return false;
		}
		$this->_rowCount = $this->db->rowCount();
		if (!$this->_isQuery) {
			return true;
		}
		return new ResultSet(
			$this->db,
			$oResult,
			$sQuery,
			$bindParams,
			null,
			$this->_model,
			$this->_saveState
		);
	}

	/**
	 * Return the number of affected rows
	 * @return int
	 */
	public function getRowCount()
	{
		return $this->_rowCount;
	}

	/**
	 * Save state of return models
	 * @return $this
	 * @var ?bool $saveState
	 */
	public function wantSaveState(?bool $saveState)
	{
		$this->_saveState = $saveState;
		return $this;
	}
}
