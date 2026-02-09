<?php

namespace CoreLib\Db;

/**
 * Build SQL WHERE conditions
 */
abstract class BaseConditionBuilder
{


	/**
	 * @var PdoDriver
	 */
	protected PdoDriver $db;

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
	protected int $_paramCounter = 0;

	/**
	 * @var array
	 */
	protected $_autoBindParams = [];

	/**
	 * @param ?PdoDriver $db
	 * @throws Exception
	 */
	public function __construct(?PdoDriver $db = null)
	{
		$this->db = $db ?? PdoDriver::defaultInstance();
	}

	/**
	 * Appends a query condition
	 * @param $condition
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function where($condition, $value = null, bool $escape = true): BaseConditionBuilder
	{
		return $this->addWhere($condition, ' AND ', $value, $escape);
	}

	/**
	 * Appends a condition to the current conditions using a AND operator
	 * @param $condition
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function andWhere($condition, $value = null, bool $escape = true): BaseConditionBuilder
	{
		return $this->addWhere($condition, ' AND ', $value, $escape);
	}

	/**
	 * Appends a condition to the current conditions using a OR operator
	 * @param string $condition
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	public function orWhere(string $condition, $value = null, bool $escape = true): BaseConditionBuilder
	{
		return $this->addWhere($condition, ' OR ', $value, $escape);
	}

	/**
	 * Appends a condition to the current conditions using an operator
	 * @param string $condition
	 * @param string $operator
	 * @param $value
	 * @param bool $escape
	 * @return $this
	 */
	private function addWhere(string $condition, string $operator, $value = null, bool $escape = true): BaseConditionBuilder
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		if (\is_array($value)) {
			// phalcon style
			$condition = $this->replacePlaceholders($condition, $value);
		} elseif (isset($value)) {
			if ($value instanceof SelectBuilder) {
				// sub select
				$escape = false;
				$value = '(' . $value->getSQLStatement() . ')';
			}
			if ($value instanceof BaseConditionBuilder) {
				// sub conditions
				$escape = false;
				$value = '(' . $value->condition . ')';
			}
			// ci style
			$condition = $this->_appendOperator($condition);
			$condition .= $escape ? $this->escapeValue($value) : $value;
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
	public function betweenWhere(string $condition, $minimum, $maximum): BaseConditionBuilder
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
	public function notBetweenWhere(string $condition, $minimum, $maximum): BaseConditionBuilder
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
	public function orBetweenWhere(string $condition, $minimum, $maximum): BaseConditionBuilder
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
	public function orNotBetweenWhere(string $condition, $minimum, $maximum): BaseConditionBuilder
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
	): BaseConditionBuilder {
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		$this->condition .= '(' . $condition . $between . $this->escapeValue($minimum) . ' AND ' . $this->escapeValue($maximum) . ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends an IN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function inWhere(string $condition, $values): BaseConditionBuilder
	{
		return $this->addInWhere($condition, ' AND ', 'IN', $values);
	}

	/**
	 * Appends an NOT IN condition to the current conditions using AND operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function notInWhere(string $condition, $values): BaseConditionBuilder
	{
		return $this->addInWhere($condition, ' AND ', 'NOT IN', $values);
	}

	/**
	 * Appends an IN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function orInWhere(string $condition, $values): BaseConditionBuilder
	{
		return $this->addInWhere($condition, ' OR ', 'IN', $values);
	}

	/**
	 * Appends an NOT IN condition to the current conditions using OR operator
	 * @param string $condition
	 * @param $values
	 * @return $this
	 */
	public function orNotInWhere(string $condition, $values): BaseConditionBuilder
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
	private function addInWhere(string $condition, string $operator, string $in, $values): BaseConditionBuilder
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		if ($values instanceof SelectBuilder) {
			$this->condition .= '(' . $condition . " $in (" . $values->getSQLStatement() . '))';
		} else {
			$this->condition .= '(' . $condition . " $in (" . $this->escapeValue($values) . '))';
		}
		$this->needOperator = true;
		return $this;
	}

	/**
	 * Appends an HAVING condition to the current conditions using AND operator
	 * @param string $condition
	 * @return $this
	 */
	public function having(string $condition): BaseConditionBuilder
	{
		return $this->addHaving($condition, ' AND ', 'HAVING');
	}

	/**
	 * Appends an NOT HAVING condition to the current conditions using AND operator
	 * @param string $condition
	 * @return $this
	 */
	public function notHaving(string $condition): BaseConditionBuilder
	{
		return $this->addHaving($condition, ' AND ', 'NOT HAVING');
	}

	/**
	 * Appends an HAVING condition to the current conditions using OR operator
	 * @param string $condition
	 * @return $this
	 */
	public function orHaving(string $condition): BaseConditionBuilder
	{
		return $this->addHaving($condition, ' OR ', 'HAVING');
	}

	/**
	 * Appends an NOT HAVING condition to the current conditions using OR operator
	 * @param string $condition
	 * @return $this
	 */
	public function orNotHaving(string $condition): BaseConditionBuilder
	{
		return $this->addHaving($condition, ' OR ', 'NOT HAVING');
	}

	/**
	 * Appends an HAVING condition to the current conditions
	 * @param string $condition
	 * @param string $operator
	 * @param string $having
	 * @return $this
	 */
	private function addHaving(string $condition, string $operator, string $having): BaseConditionBuilder
	{
		if ($this->needOperator) {
			$this->condition .= $operator;
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
	public function likeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	public function andLikeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	public function orLikeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	public function notLikeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	public function andNotLikeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	public function orNotLikeWhere(string $identifier, $value, bool $escape = true): BaseConditionBuilder
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
	): BaseConditionBuilder {
		if ($this->needOperator) {
			$this->condition .= $operator;
		}
		$this->condition .= '(';
		$this->condition .= $identifier;
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
	public function groupStart(): BaseConditionBuilder
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
	public function orGroupStart(): BaseConditionBuilder
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
	public function notGroupStart(): BaseConditionBuilder
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
	public function orNotGroupStart(): BaseConditionBuilder
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
	public function groupEnd(): BaseConditionBuilder
	{
		$this->condition .= ')';
		$this->needOperator = true;
		return $this;
	}

	/**
	 * No operator function. Useful to build flexible chains
	 * @return $this
	 */
	public function noop(): BaseConditionBuilder
	{
		return $this;
	}

	/**
	 * Append CI style operator to condition string
	 * @param string $sCondition
	 * @return string
	 */
	private function _appendOperator(string $sCondition): string
	{
		$sCondition = rtrim($sCondition);
		$iIndex = strlen($sCondition) - 1;
		if ($iIndex >= 0) {
			switch ($sCondition[$iIndex]) {
				case '=':
				case '<':
				case '>':
					break;
				default:
					$sCondition .= ' =';
					break;
			}
			$sCondition .= ' ';
		}
		return $sCondition;
	}

	/**
	 * Replace placeholders with escaped values
	 * Supports both positional (?) and named (:name) placeholders
	 * @param string $sCondition
	 * @param array|null $aBindParams
	 * @return string
	 */
	protected function replacePlaceholders(string $sCondition, ?array $aBindParams): string
	{
		if (!empty($aBindParams)) {
			foreach ($aBindParams as $mKey => $mValue) {
				$escapedValue = $this->serializeScalar($mValue);

				if (is_int($mKey)) {
					$sCondition = str_replace('?', $escapedValue, $sCondition);
				} else {
					$sCondition = str_replace(':' . $mKey, $escapedValue, $sCondition);
				}
			}
		}
		return $sCondition;
	}

	/**
	 * Serialize a value to SQL (handles SqlNode instances)
	 * @param mixed $value
	 * @param bool $param Whether to serialize as a bind parameter
	 * @return string
	 */
	protected function serializeScalar($value, bool $param = false): string
	{
		// SqlNode instances serialize themselves
		if ($value instanceof SqlNode) {
			return $value->toSql(
				$this->db->getDriver(),
				fn($v, $p = false) => $this->serializeScalar($v, $p)
			);
		}

		if ($param) {
			// Param mode: create bind parameter
			$name = '__p' . (++$this->_paramCounter);
			$this->_autoBindParams[$name] = $value;
			return ':' . $name;
		} else {
			// Literal mode: escape to SQL literal
			return $this->escapeValue($value);
		}

	}

	/**
	 * Escape a value
	 * @param mixed $mValue
	 * @return string
	 */
	protected function escapeValue($mValue)
	{
		// PostgreSQL Array Support
		if (is_array($mValue)) {
			// special array with value + escape flag
			$isSpecialArray =
				count($mValue) === 2 &&
				isset($mValue['value']) &&
				isset($mValue['escape']);

			if ($isSpecialArray) {
				return $mValue['escape']
					? $this->escapeValue($mValue['value'])
					: (string) $mValue['value'];
			}

			$result = "";
			$sep = "";
			foreach ($mValue as $v) {
				$result .= $sep;
				$sep = ",";
				$result .= $this->escapeValue($v);
			}
			return $result;
		}

		// scalars
		if ($mValue === null) {
			return 'NULL';
		}

		if (is_int($mValue) || is_float($mValue)) {
			return $mValue;
		}

		if (is_bool($mValue)) {
			if ($this->db->getDriver() === 'pgsql') {
				return $mValue ? 'TRUE' : 'FALSE';
			}
			return $mValue ? '1' : '0';
		}

		if ($mValue instanceof SelectBuilder) {
			return '(' . $mValue->getSQLStatement() . ')';
		}

		if ($mValue instanceof BaseConditionBuilder) {
			return '(' . $mValue->condition . ')';
		}

		return $this->db->quote((string) $mValue);
	}
}
