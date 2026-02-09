<?php

namespace CoreLib\Db;

/**
 * Class to cache models
 */
class ModelCache
{
	/**
	 * @var array
	 */
	private $_cache;

	/**
	 * ModelCache constructor.
	 * @param array|null $cache
	 */
	public function __construct($cache = null)
	{
		$this->_cache = $cache ?: [];
	}

	/**
	 * @param string $name
	 * @param string|null $source
	 * @param string|null $schema
	 * @return $this
	 */
	public function add($name, $source = null, $schema = null)
	{
		if (empty($source)) {
			// MyTable -> my_table
			$source = strtolower(preg_replace(
				['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'],
				'$1_$2',
				$name
			));
		}
		$this->_cache[$name] = [
			'source' => $source,
			'schema' => $schema,
		];
		return $this;
	}

	/**
	 * @param string $name
	 * @return array|null
	 */
	public function get($name)
	{
		return $this->_cache[$name] ?? null;
	}


	public function export()
	{
		return var_export($this->_cache, true);
	}
}
