<?php
namespace CoreLib\Db;

/**
 * Class to build sql UPDATE queries
 * @example
 *
 * (new UpdateBuilder())
 *     ->update('Race')
 *       ->values(['status' => 'open', 'start_time' => null])
 *     ->where('id', 123)
 *     ->execute()
 *
 * (new UpdateBuilder())
 *     ->update('Race')
 *       ->columns(['status = :status:', 'start_time = NULL'])
 *     ->where('id = :id:')
 *       ->bind(['status' => 'open', 'id' => 123])
 *     ->execute()
 *
 */
class UpdateBuilder extends BaseBuilder
{
	/**
	 * @var string
	 */
	protected $_tableName;

	/**
	 * @param string $sModel
	 * @param ?string $sAlias
	 * @return $this
	 * @throws Exception
	 */
	public function update($sModel, $sAlias = null)
	{
		$this->_model = null;
		$this->_tableName = $this->_protectIdentifier($sModel, $sAlias, true);
		return $this;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function _buildSqlStatement(): string
	{
		if (empty($this->_tableName)) {
			throw new Exception('No table set');
		}
		if (empty($this->_columns)) {
			if (empty($this->_aValues[0])) {
				if (empty($this->_bindParams)) {
					throw new Exception('No columns set');
				} else {
					$this->_columns = array_keys($this->_bindParams);
				}
			}
		}
		$sStatement = 'UPDATE ';
		$sStatement .= $this->_tableName;
		if (!empty($this->_join)) {
			foreach ($this->_join as $aJoin) {
				if (!empty($aJoin['type'])) {
					$sStatement .= ' ';
					$sStatement .= $aJoin['type'];
				}
				$sStatement .= ' JOIN ';
				$sStatement .= $aJoin['table'];
				$sStatement .= ' ON (';
				$sStatement .= $this->_compileCondition($aJoin['conditions']);
				$sStatement .= ')';
			}
		}
		$sStatement .= ' SET ';
		if (!empty($this->_columns)) {
			foreach ($this->_columns as $sColumn) {
				$iPos = strpos($sColumn, '=');
				if ($iPos > 0) {
					$sValue = ltrim(substr($sColumn, $iPos + 1));
					$sColumn = rtrim(substr($sColumn, 0, $iPos));
				} else {
					$sValue = ':' . $sColumn;
				}
				$sStatement .= $this->_protectIdentifier($sColumn);
				$sStatement .= '=';
				$sStatement .= $sValue;
				$sStatement .= ', ';
			}
		} else {
			foreach ($this->_values[0] as $sColumn => $mValue) {
				$sStatement .= $this->_protectIdentifier($sColumn);
				$sStatement .= '=';
				// Serialize SqlNode instances, pass through already escaped values
				if ($mValue instanceof SqlNode) {
					$sStatement .= $this->serializeScalar($mValue);
				} else {
					// Already escaped by values()/set(), or raw value if $bEscape=false
					$sStatement .= $mValue;
				}
				$sStatement .= ', ';
			}
		}
		$sStatement = substr($sStatement, 0, -2);
		if (!empty($this->condition)) {
			$sStatement .= ' WHERE ' . $this->_compileCondition($this->condition);
		}
		if (!empty($this->_orderBy)) {
			$aColumns = \is_array($this->_orderBy) ? $this->_orderBy : explode(',', $this->_orderBy);
			foreach ($aColumns as $iIndex => $sColumn) {
				$aColumns[$iIndex] = $this->_protectIdentifier($sColumn, null, false, true);
			}
			$sStatement .= ' ORDER BY ';
			$sStatement .= implode(', ', $aColumns);
		}
		if (isset($this->_limit)) {
			$sStatement .= ' LIMIT ' . $this->_limit;
		}
		return $sStatement;
	}
}