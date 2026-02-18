<?php

namespace Merlin\Cli;

use Merlin\Cli\Exceptions\ActionNotFoundException;
use Merlin\Cli\Exceptions\InvalidTaskException;
use Merlin\Cli\Exceptions\TaskNotFoundException;

class Console
{
	protected string $defaultTask = "MainTask";

	protected string $defaultAction = "mainAction";

	protected string $namespace = "App\\Tasks";

	protected bool $parseParams = true;

	public function getDefaultTask(): string
	{
		return $this->defaultTask;
	}

	public function setDefaultTask(string $defaultTask): void
	{
		if (empty($defaultTask)) {
			throw new Exception("Default task cannot be empty");
		}
		$this->defaultTask = $defaultTask;
	}

	public function getDefaultAction(): string
	{
		return $this->defaultAction;
	}

	public function setDefaultAction(string $defaultAction): void
	{
		if (empty($defaultAction)) {
			throw new Exception("Default action cannot be empty");
		}
		$this->defaultAction = $defaultAction;
	}

	public function getNamespace(): string
	{
		return $this->namespace;
	}

	public function setNamespace(string $namespace): void
	{
		if (!empty($namespace)) {
			$namespace = rtrim(
				$namespace,
				'\\'
			) . '\\';
		}
		$this->namespace = $namespace;
	}

	public function shouldParseParams(): bool
	{
		return $this->parseParams;
	}

	public function setParseParams(bool $parseParams): void
	{
		$this->parseParams = $parseParams;
	}

	/**
	 * @param string|null $task
	 * @param string|null $action
	 * @param array $params
	 * @return mixed
	 * @throws TaskNotFoundException
	 * @throws ActionNotFoundException
	 */
	public function process(
		?string $task = null,
		?string $action = null,
		array $params = []
	): mixed {
		// Determine class name
		if (!empty($task)) {
			$className = self::camelize($task) . 'Task';
		} else {
			$className = $this->defaultTask;
		}

		// Determine method name
		if (!empty($action)) {
			$methodName = self::camelize($action) . 'Action';
		} else {
			$methodName = $this->defaultAction;
		}

		// Prepend namespace if set
		if (!empty($this->namespace)) {
			$className = $this->namespace . $className;
		}

		// Check if class and method exist
		if (!class_exists($className)) {
			throw new TaskNotFoundException(
				"Task {$className} not found"
			);
		}

		// Instantiate class and check if it's a Task
		$classInstance = new $className();
		if (!$classInstance instanceof Task) {
			throw new InvalidTaskException(
				"Class {$className} not an instance of Task"
			);
		}

		// Check if method exists
		if (!method_exists($classInstance, $methodName)) {
			throw new ActionNotFoundException(
				"Action {$className}->{$methodName}() not found"
			);
		}

		// Get parameters
		if ($this->parseParams) {
			foreach ($params as $index => $param) {
				if (ctype_digit($param)) {
					$params[$index] = (int) $param;
					continue;
				}
				if (is_numeric($param)) {
					$params[$index] = (float) $param;
					continue;
				}
				if (strcasecmp($param, 'TRUE') === 0) {
					$params[$index] = true;
					continue;
				}
				if (strcasecmp($param, 'FALSE') === 0) {
					$params[$index] = false;
					continue;
				}
				if (strcasecmp($param, 'NULL') === 0) {
					$params[$index] = null;
					continue;
				}
			}
		}

		// Execute action
		return $classInstance->{$methodName}(...$params);
	}

	protected static function camelize(string $string, bool $beginUpper = true): string
	{
		// Unify possible separators
		$string = str_replace(
			['-', '_', '.'],
			' ',
			$string
		);

		// Split into words, remove empty
		$result = "";
		foreach (explode(' ', $string) as $part) {
			if ($part !== '') {
				// Normalize each word
				if (!$beginUpper && $result === "") {
					// Enforce lowerCamelCase
					$result .= strtolower($part);
					continue;
				}
				$result .= strtoupper($part[0]) . strtolower(substr($part, 1));
			}
		}

		return $result;
	}

}
