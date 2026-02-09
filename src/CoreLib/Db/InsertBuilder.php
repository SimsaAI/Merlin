<?php
/** @noinspection SqlIdentifier */
/** @noinspection SqlResolve */

namespace CoreLib\Db;

/**
 * Class to build sql INSERT queries
 * @example
 *
 * (new InsertBuilder())
 *     ->into('Country')
 * 	   ->values(['iso2' => 'Z1', 'iso3' => 'ZZ1'])
 * 	   ->values(['iso2' => 'Z2', 'iso3' => 'ZZ2'])
 *     ->execute()
 *
 * (new InsertBuilder())
 *     ->into('Country')
 * 	   ->values(['iso2' => 'Z1', 'iso3' => 'ZZ1', 'name' => 'Zoolooland'])
 *     ->upsert(['name' => 'Zoolooland'])
 *     ->execute()
 *
 */
class InsertBuilder extends BaseBuilder
{
	/**
	 * @var string
	 */
	protected $_table;

	/**
	 * @var bool
	 */
	protected $_replaceInto;

	/**
	 * @var bool
	 */
	protected $_upsert;

	/**
	 * @var bool
	 */
	protected $_ignore;

	/**
	 * @var array
	 */
	protected $_updateValues;

	/**
	 * @var array|string
	 */
	protected $_conflictTarget;

	protected $_returning;

	/**
	 * @param string $model
	 * @return $this
	 * @throws Exception
	 */
	public function insert($model)
	{
		$this->_model = null;
		$this->_table = $this->_protectIdentifier($model, null, true);
		$this->_replaceInto = false;
		return $this;
	}

	/**
	 * @param string $model
	 * @return $this
	 * @throws Exception
	 */
	public function replace($model)
	{
		$this->_model = null;
		$this->_table = $this->_protectIdentifier($model, null, true);
		$this->_replaceInto = true;
		return $this;
	}

	/**
	 * @param array|bool $updateValues
	 * @param bool $escape
	 * @return $this
	 */
	public function upsert($updateValues, $escape = true)
	{
		$this->_upsert = (bool) $updateValues;
		if (is_array($updateValues)) {
			if ($escape) {
				foreach ($updateValues as $sColumn => $mValue) {
					// SqlNode instances are stored as-is, serialized later
					if (!($mValue instanceof SqlNode)) {
						$updateValues[$sColumn] = $this->escapeValue($mValue);
					}
				}
			}
			$this->_updateValues = $updateValues;
		} else {
			$this->_updateValues = null;
		}
		return $this;
	}

	/**
	 * @param bool $ignore
	 * @return $this
	 */
	public function ignore($ignore)
	{
		$this->_ignore = $ignore;
		return $this;
	}

	/**
	 * @param array|string $columnsOrConstraint
	 * @return $this
	 */
	public function conflict($columnsOrConstraint)
	{
		$this->_conflictTarget = $columnsOrConstraint;
		return $this;
	}

	public function returning($columns)
	{
		if ($this->db->getDriver() !== 'pgsql') {
			throw new Exception("RETURNING is only supported by PostgreSQL");
		}
		if (!empty($columns)) {
			$this->_returning = is_array($columns) ? $columns : explode(',', $columns);
		} else {
			$this->_returning = null;
		}
		return $this;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function _buildSqlStatement(): string
	{
		if (empty($this->_table)) {
			throw new Exception('No table set');
		}
		if (empty($this->_values)) {
			$aFields = $this->_bindParams;
			if (empty($aFields)) {
				throw new Exception('No values or bind parameters set');
			}
			if (empty($this->_columns)) {
				$this->columns($aFields);
			}
		} else {
			if (empty($this->_columns)) {
				$this->columns(array_keys(reset($this->_values)));
			}
		}
		if ($this->_replaceInto) {
			switch ($this->db->getDriver()) {
				case 'mysql':
					$sStatement = 'REPLACE INTO ';
					break;
				case 'sqlite':
					$sStatement = 'INSERT OR REPLACE ';
					break;
				case 'pgsql':
					$sStatement = 'INSERT INTO ';
					$bUpsert = true;
					break;
				default:
					throw new Exception("Replace not implemented for or by this driver");
			}
		} elseif ($this->_ignore) {
			switch ($this->db->getDriver()) {
				case 'mysql':
					$sStatement = 'INSERT IGNORE INTO ';
					break;
				case 'sqlite':
					$sStatement = 'INSERT OR IGNORE INTO ';
					break;
				case 'pgsql':
					$sStatement = 'INSERT INTO ';
					$sConflictClause = ' ON CONFLICT DO NOTHING ';
					break;
				default:
					throw new Exception("Replace not implemented for or by this driver");
			}
		} else {
			$sStatement = 'INSERT INTO ';
		}
		$sStatement .= $this->_table;
		$sStatement .= ' (';
		$sStatement .= implode(',', $this->_protectColumns());
		$sStatement .= ') VALUES ';
		if (isset($aFields)) {
			$sStatement .= '(:';
			$sStatement .= implode(',:', $aFields);
			$sStatement .= ')';
		} else {
			foreach ($this->_values as $aValues) {
				$sStatement .= '(';
				// Serialize values: already escaped strings pass through, SqlNode instances serialize now
				$serializedValues = [];
				foreach ($aValues as $mValue) {
					if ($mValue instanceof SqlNode) {
						$serializedValues[] = $this->serializeScalar($mValue);
					} else {
						// Already escaped by values()/set(), or raw value if $bEscape=false
						$serializedValues[] = $mValue;
					}
				}
				$sStatement .= implode(', ', $serializedValues);
				$sStatement .= '),';
			}
			$sStatement = substr($sStatement, 0, -1);
		}
		if ($this->_upsert || !empty($bUpsert)) {
			switch ($this->db->getDriver()) {
				case 'mysql':
					$sStatement .= ' ON DUPLICATE KEY UPDATE ';
					break;
				case 'sqlite':
					$sStatement .= ' ON CONFLICT DO UPDATE SET ';
					break;
				case 'pgsql':
					if (empty($this->_conflictTarget)) {
						throw new Exception("PostgreSQL requires a conflict target for UPSERT");
					}
					if (is_array($this->_conflictTarget)) {
						$sStatement .= ' ON CONFLICT (' . implode(',', $this->_protectColumns($this->_conflictTarget)) . ') DO UPDATE SET ';
					} else {
						$sStatement .= ' ON CONFLICT ON CONSTRAINT ' . $this->_protectIdentifier($this->_conflictTarget) . ' DO UPDATE SET ';
					}
					break;
				default:
					throw new Exception("Upsert not implemented for or by this driver");
			}
			$sep = '';
			if (!empty($this->_updateValues)) {
				foreach ($this->_updateValues as $sColumn => $mValue) {
					$sStatement .= $sep;
					$sStatement .= $this->_protectIdentifier($sColumn);
					$sStatement .= '=';
					// Serialize SqlNode instances in update values
					if ($mValue instanceof SqlNode) {
						$sStatement .= $this->serializeScalar($mValue);
					} else {
						// Already escaped by upsert() method
						$sStatement .= $mValue;
					}
					$sep = ',';
				}
			} elseif (isset($aFields)) {
				foreach ($aFields as $sColumn) {
					$sStatement .= $sep;
					$sStatement .= $this->_protectIdentifier($sColumn);
					$sStatement .= '=:';
					$sStatement .= $sColumn;
					$sep = ',';
				}
			} else {
				if (count($this->_values) > 1) {
					throw new Exception('Upsert with multiple value sets is not supported without explicit update values');
				}
				foreach ($this->_values[0] as $sColumn => $mValue) {
					$sStatement .= $sep;
					$sStatement .= $this->_protectIdentifier($sColumn);
					$sStatement .= '=';
					// Serialize SqlNode instances in upsert values
					if ($mValue instanceof SqlNode) {
						$sStatement .= $this->serializeScalar($mValue);
					} else {
						// Already escaped by values()/set()
						$sStatement .= $mValue;
					}
					$sep = ',';
				}
			}
		} elseif (!empty($sConflictClause)) {
			$sStatement .= $sConflictClause;
		}
		if (!empty($this->_returning)) {
			$sStatement .= ' RETURNING ';
			$sStatement .= implode(', ', $this->_protectColumns($this->_returning));
			$this->_isQuery = true;
		} else {
			$this->_isQuery = false;
		}
		return $sStatement;
	}
}