<?php
namespace CoreLib\Db;

/**
 * Build conditions for ->where(..) function
 */
class ConditionBuilder extends BaseConditionBuilder
{
	/**
	 * @var string
	 */
	protected string $_finalCondition = '';

	/**
	 * Bind parameters for the condition
	 * @param array $bindParams
	 * @return $this
	 */
	public function bind(array $bindParams): ConditionBuilder
	{
		$bindParams = array_merge($this->_autoBindParams, $bindParams);
		$this->_finalCondition = $this->replacePlaceholders(
			$this->condition,
			$bindParams
		);
		return $this;
	}

	/**
	 * Get the condition
	 * @return string
	 */
	public function get(): string
	{
		return $this->_finalCondition ?: $this->condition;
	}
}
