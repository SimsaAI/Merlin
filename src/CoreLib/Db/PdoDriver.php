<?php

namespace CoreLib\Db;

use PDO;
use PDOStatement;
use CoreLib\Mvc\Model;
use Psr\Log\LoggerInterface;

/**
 * Class PdoDriver
 */
class PdoDriver
{

	/**
	 * @var PdoDriver
	 */
	public static $defaultInstance;

	/**
	 * @var bool
	 */
	public static $automaticReconnect;

	/**
	 * @throws Exception
	 */
	public static function defaultInstance(): PdoDriver
	{
		if (!isset(self::$defaultInstance)) {
			throw new Exception("No database connection established");
		}
		return self::$defaultInstance;
	}

	/**
	 * @var string
	 */
	private $connectString;

	/**
	 * @var string
	 */
	private $user;

	/**
	 * @var string
	 */
	private $driverName;

	/**
	 * @var string
	 */
	private $auth;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $statement;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var int
	 */
	protected $transactionLevel = 0;

	/**
	 * @var string
	 */
	protected $quoteChar = '"';

	/**
	 * @param array $config
	 */
	public function __construct($config)
	{

		$this->options = [
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		];

		if (!empty($config['options'])) {
			$this->options = array_merge($this->options, $config['options']);
		}

		$this->user = $config['user'] ?? $config['username'];
		$this->auth = $config['auth'] ?? $config['password'];

		$this->driverName = $config['driver'] ?? 'mysql';
		$this->connectString = $this->driverName . ':';

		switch ($this->driverName) {
			case 'mysql':
				$this->options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = 1;
				$this->quoteChar = '`';
				break;
		}

		unset(
			$config['options'],
			$config['driver'],
			$config['user'],
			$config['username'],
			$config['auth'],
			$config['password']
		);

		foreach ($config as $key => $value) {
			$this->connectString .= $key . '=' . $value . ';';
		}

		if (!isset(self::$defaultInstance)) {
			self::$defaultInstance = $this;
		}

		$this->connect();
	}

	/**
	 *
	 */
	public function connect()
	{
		$this->pdo = new PDO($this->connectString, $this->user, $this->auth, $this->options);
		$this->transactionLevel = 0;
	}

	/**
	 * @param LoggerInterface $_logger
	 */
	public function setLogger($_logger)
	{
		$this->logger = $_logger;
	}

	/**
	 * @param string $sStatement
	 * @param array|null $params
	 * @return bool|PDOStatement
	 * @throws Exception
	 */
	public function query($sStatement, $params = null)
	{
		retry:
		$fTimeStart = \microtime(true);
		try {
			$aFuncParams = \func_get_args();
			\array_shift($aFuncParams); // throw the $sStatement argument
			if (\count($aFuncParams) === 1 && \is_array($aFuncParams[0])) {
				$aFuncParams = $aFuncParams[0];
			}
			if (!empty($aFuncParams)) {
				$oStatement = $this->pdo->prepare($sStatement);
				$oStatement->execute($aFuncParams);
			} else {
				$oStatement = $this->pdo->query($sStatement);
			}
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			goto retry;
		} finally {
			if ($this->logger) {
				$sElapsedMs = \sprintf('%0.3f', (\microtime(true) - $fTimeStart) * 1000);
				$sStatement = \preg_replace('/^\s+/m', '', $sStatement);
				$sLogMessage = 'Query: ' . $sStatement . ' -- (' . $sElapsedMs . ' ms)';
				if (isset($aFuncParams)) {
					$sLogMessage .= ' Arguments: ' . \json_encode($aFuncParams);
				}
				$this->logger->debug($sLogMessage);
			}
		}
		$this->statement = $oStatement;
		return ($oStatement->columnCount() > 0) ? $oStatement : true;
	}

	/**
	 * @param string $query
	 * @return PDOStatement
	 * @throws \Exception
	 */
	public function prepare($query)
	{
		retry:
		try {
			if ($this->logger) {
				$time_start = microtime(true);
				$sth = $this->pdo->prepare($query);
				$runtime = sprintf('%0.3f', (microtime(true) - $time_start) * 1000);
				$message = 'Prepare: ' . $query . ' -- (' . $runtime . ' ms)';
				$this->logger->debug($message);
			} else {
				$sth = $this->pdo->prepare($query);
			}
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			goto retry;
		}
		$this->statement = $sth;
		return $sth;
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return bool|null|PDOStatement
	 * @throws \Exception
	 */
	public function execute($sth = null)
	{
		try {
			$time_start = microtime(true);
			if (!empty($sth)) {
				$this->statement = $sth;
			}
			$params = func_get_args();
			array_shift($params); // throw the $sth argument
			$this->statement->execute($params);
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			throw $oException;
		} finally {
			if ($this->logger) {
				$runtime = sprintf('%0.3f', (microtime(true) - $time_start) * 1000);
				$message = 'Execute (' . $runtime . ' ms)';
				if (!empty($params)) {
					$message .= ' Arguments: ' . json_encode($params);
				}
				$this->logger->debug($message);
			}
		}
		return ($this->statement->columnCount() > 0) ? $this->statement : true;
	}

	/**
	 * @param \PDOException $oException
	 * @throws Exception
	 */
	private function _processException($oException)
	{
		if ($this->logger) {
			$this->logger->error($oException->getMessage());
		}
		$bInTransaction = !empty($this->transactionLevel);
		switch ($oException->errorInfo[1]) {
			case 1213: // Error: 1213 SQLSTATE: 40001 (ER_LOCK_DEADLOCK)
			case '40P01': // PGSQL: 40P01 – deadlock_detected
			case 40001: // Deadlock or timeout with automatic rollback occurred.
				if ($bInTransaction) {
					throw new TransactionLostException(
						"Deadlock found when trying to get lock; try restarting transaction",
						40001,
						$oException
					);
				}
				// Re-run last command
				return;
			case 2006: // Error: 2006 (CR_SERVER_GONE_ERROR)
			case 2013: // Error: 2013 (CR_SERVER_LOST)
			case 8001:
			case 8004:
			case 8006: // PGSQL: 8006 Connection Exception
				if (self::$automaticReconnect) {
					$iCounter = 1;
					while (true) {
						if ($this->logger) {
							$this->logger->notice(sprintf("Reconnecting db try # %d ...", $iCounter));
						}
						try {
							$this->connect();
							break;
						} catch (\Exception $ex) {
							$iCounter++;
							sleep(1);
						}
					}
					if ($bInTransaction) {
						throw new TransactionLostException(
							"Lost transaction during reconnect",
							8001,
							$oException
						);
					}
					// Re-run last command
					return;
				}
				break;
		}
		throw $oException;
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return mixed
	 */
	public function fetchArray($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->fetch(PDO::FETCH_BOTH);
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return mixed
	 */
	public function fetchAssoc($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return mixed
	 */
	public function fetchRow($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->fetch(PDO::FETCH_NUM);
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return mixed
	 */
	public function fetchObject($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->fetch(PDO::FETCH_OBJ);
	}

	/**
	 * @param PDOStatement|null $sth
	 * @param int $mode
	 * @return array
	 */
	public function fetchAll($sth = null, $mode = PDO::FETCH_BOTH)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->fetchAll($mode);
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return bool
	 */
	public function freeResult($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return $sth->closeCursor();
	}

	/**
	 * @param string $query
	 * @return mixed
	 */
	public function selectRow($query)
	{
		$sth = call_user_func_array([$this, 'query'], func_get_args());
		$row = $sth->fetch(PDO::FETCH_BOTH);
		$sth->closeCursor();
		return $row;
	}

	/**
	 * @param string $query
	 * @return mixed
	 */
	public function selectAll($query)
	{
		$sth = call_user_func_array([$this, 'query'], func_get_args());
		$result = $sth->fetchAll(PDO::FETCH_BOTH);
		$sth->closeCursor();
		return $result;
	}

	/**
	 * @param PDOStatement|null $sth
	 * @return int
	 */
	public function rowCount($sth = null)
	{
		if (empty($sth)) {
			$sth = $this->statement;
		}
		return isset($sth) ? $sth->rowCount() : 0;
	}

	/**
	 * @return string
	 */
	public function lastInsertId($table = null, $field = null)
	{
		if ($this->driverName === 'pgsql' && !empty($table) && !empty($field)) {
			if ($table instanceof Model) {
				$schema = $table->schema();
				$table = $table->source();
				if (!empty($schema)) {
					$table = "$schema.$table";
				}
			}
			$stmt = $this->pdo->prepare(
				"SELECT currval(pg_catalog.pg_get_serial_sequence(:table, :field))"
			);
			$stmt->execute([
				':table' => $table,
				':field' => $field
			]);
			return $stmt->fetchColumn();
		}
		return $this->pdo->lastInsertId();
	}

	public function begin($nesting = true)
	{
		try {
			$time_start = microtime(true);
			$this->transactionLevel++;
			if ($this->transactionLevel === 1) {
				$result = $this->pdo->beginTransaction();
			} elseif ($nesting) {
				switch ($this->driverName) {
					case 'mysql':
					case 'pgsql':
					case 'sqlite':
						$result = $this->pdo->exec(
							"SAVEPOINT trans$this->transactionLevel"
						);
						break;
					default:
						$result = $this->pdo->beginTransaction();
				}
			} else {
				$result = false;
			}
			return $result;
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			throw $oException;
		} finally {
			if ($this->logger && $result) {
				$runtime = sprintf('%0.3f', (microtime(true) - $time_start) * 1000);
				$message = "Begin transaction #$this->transactionLevel ($runtime ms)";
				$this->logger->debug($message);
			}
		}
	}

	public function commit($nesting = true)
	{
		if ($this->transactionLevel === 0) {
			throw new Exception("There is no active transaction");
		}
		try {
			$time_start = microtime(true);
			$level = $this->transactionLevel--;
			if ($level === 1) {
				$result = $this->pdo->commit();
			} elseif ($nesting) {
				switch ($this->driverName) {
					case 'mysql':
					case 'pgsql':
					case 'sqlite':
						$result = $this->pdo->exec("RELEASE SAVEPOINT trans$level");
						break;
					default:
						$result = $this->pdo->commit();
				}
			} else {
				$result = false;
			}
			return $result;
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			throw $oException;
		} finally {
			if ($this->logger && $result) {
				$runtime = sprintf('%0.3f', (microtime(true) - $time_start) * 1000);
				$message = "Commit transaction #$level ($runtime ms)";
				$this->logger->debug($message);
			}
		}
	}

	public function rollback($nesting = true)
	{
		if ($this->transactionLevel === 0) {
			throw new Exception("There is no active transaction");
		}
		try {
			$time_start = microtime(true);
			$level = $this->transactionLevel--;
			if ($level === 1) {
				$result = $this->pdo->rollBack();
			} elseif ($nesting) {
				switch ($this->driverName) {
					case 'mysql':
					case 'pgsql':
					case 'sqlite':
						$result = $this->pdo->exec("ROLLBACK TO SAVEPOINT trans$level");
						break;
					default:
						$result = $this->pdo->rollBack();
				}
			} else {
				$result = false;
			}
			return $result;
		} catch (\PDOException $oException) {
			$this->_processException($oException);
			throw $oException;
		} finally {
			if ($this->logger && $result) {
				$runtime = sprintf('%0.3f', (microtime(true) - $time_start) * 1000);
				$message = "Rollback transaction #$level ($runtime ms)";
				$this->logger->debug($message);
			}
		}
	}

	/**
	 * @param ?string $str
	 * @return string
	 */
	public function quote($str)
	{
		if ($str === null) {
			return 'NULL';
		} else {
			return $this->pdo->quote($str);
		}
	}

	/**
	 * @param ?string $str
	 * @return string
	 */
	public function escapeString($str, $type = PDO::PARAM_STR)
	{
		if ($str !== null) {
			$str = $this->pdo->quote($str, $type);
			return substr($str, 1, -1);
		} else {
			return '';
		}
	}

	/**
	 * @param ...?string $arg
	 * @return string
	 */
	public function quoteIdentifier()
	{
		$quoted = '';
		$sep = '';
		foreach (func_get_args() as $arg) {
			if ($arg === null) {
				continue;
			}
			$quoted .= $sep;
			if ($arg === '*') {
				$quoted .= '*';
			} else {
				$quoted .= $this->quoteChar;
				$quoted .= str_replace(
					$this->quoteChar,
					$this->quoteChar . $this->quoteChar,
					$arg
				);
				$quoted .= $this->quoteChar;
			}
			$sep = '.';
		}
		return $quoted;
	}

	/**
	 * @return PDO
	 */
	public function getInternalHandler()
	{
		return $this->pdo;
	}

	/**
	 * Select builder
	 * @return SelectBuilder
	 */
	public function selectBuilder()
	{
		return new SelectBuilder($this);
	}

	/**
	 * Insert builder
	 * @return InsertBuilder
	 */
	public function insertBuilder()
	{
		return new InsertBuilder($this);
	}

	/**
	 * Update builder
	 * @return UpdateBuilder
	 */
	public function updateBuilder()
	{
		return new UpdateBuilder($this);
	}

	/**
	 * Delete builder
	 * @return DeleteBuilder
	 */
	public function deleteBuilder()
	{
		return new DeleteBuilder($this);
	}

	/**
	 * @return string
	 */
	public function getDriver()
	{
		return $this->driverName;
	}
}
