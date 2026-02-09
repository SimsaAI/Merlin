<?php

namespace CoreLib\Cli;

use CoreLib\Exception;

class Console
{
	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws Exception
	 */
	public function handle($arguments = [])
	{
		$task = empty($arguments['task']) ? 'main' : $arguments['task'];
		$action = empty($arguments['action']) ? 'main' : $arguments['action'];
		$className = self::capitalizeClass($task) . 'Task';
		$methodName = self::capitalizeMethod($action) . 'Action';
		$params = isset($arguments['params']) ? $arguments['params'] : [];
		if (!class_exists($className)) {
			throw new Exception("Task {$className} not found");
		}
		$classInstance = new $className();
		if (!$classInstance instanceof Task) {
			throw new Exception("Class {$className} not an instance of Task");
		}
		if (!method_exists($classInstance, $methodName)) {
			throw new Exception("Action {$className}->{$methodName}() not found");
		}
		return call_user_func_array([$classInstance, $methodName], $params);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function capitalizeClass($string)
	{
		if (strpos($string, '-') === false && ctype_upper($string[0])) {
			return $string;
		}
		$result = '';
		foreach (explode('-', $string) as $word) {
			$result .= mb_strtoupper(mb_substr($word, 0, 1)) . mb_strtolower(mb_substr($word, 1));
		}
		return $result;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function capitalizeMethod($string)
	{
		if (strpos($string, '-') === false && ctype_lower($string[0])) {
			return $string;
		}
		$result = '';
		foreach (explode('-', $string) as $index => $word) {
			if ($index > 0) {
				$result .= mb_strtoupper(mb_substr($word, 0, 1)) . mb_strtolower(mb_substr($word, 1));
			} else {
				$result .= mb_strtolower($word);
			}
		}
		return $result;
	}
}
