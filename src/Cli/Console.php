<?php

namespace Merlin\Cli;

use Merlin\Cli\Exceptions\ActionNotFoundException;
use Merlin\Cli\Exceptions\InvalidTaskException;
use Merlin\Cli\Exceptions\TaskNotFoundException;

/**
 * Console entry point for dispatching CLI tasks.
 *
 * Resolves a task class (a subclass of {@see Task}) and an action method based on
 * the command-line arguments, converts string arguments to appropriate scalar types,
 * and invokes the action.
 */
class Console
{
	protected string $defaultTask = "MainTask";

	protected string $defaultAction = "mainAction";

	protected string $namespace = "App\\Tasks";

	protected bool $parseParams = true;

	/**
	 * Get the default task class name used when no task is specified on the command line.
	 *
	 * @return string Default task class name (without namespace), e.g. "MainTask".
	 */
	public function getDefaultTask(): string
	{
		return $this->defaultTask;
	}

	/**
	 * Set the default task class name used when no task is specified on the command line.
	 *
	 * @param string $defaultTask Task class name (without namespace), e.g. "MainTask".
	 * @throws Exception If the given name is empty.
	 */
	public function setDefaultTask(string $defaultTask): void
	{
		if (empty($defaultTask)) {
			throw new Exception("Default task cannot be empty");
		}
		$this->defaultTask = $defaultTask;
	}

	/**
	 * Get the default action method name used when no action is specified on the command line.
	 *
	 * @return string Default action method name (without namespace), e.g. "mainAction".
	 */
	public function getDefaultAction(): string
	{
		return $this->defaultAction;
	}

	/**
	 * Set the default action method name used when no action is specified on the command line.
	 *
	 * @param string $defaultAction Action method name, e.g. "mainAction".
	 * @throws Exception If the given name is empty.
	 */
	public function setDefaultAction(string $defaultAction): void
	{
		if (empty($defaultAction)) {
			throw new Exception("Default action cannot be empty");
		}
		$this->defaultAction = $defaultAction;
	}

	/**
	 * Get the PHP namespace used to locate task classes.
	 *
	 * @return string Namespace string (always ends with a backslash), e.g. "App\\Tasks\\".
	 */
	public function getNamespace(): string
	{
		return $this->namespace;
	}

	/**
	 * Set the PHP namespace used to locate task classes.
	 *
	 * A trailing backslash is added automatically if missing.
	 * Pass an empty string to disable namespace prefixing.
	 *
	 * @param string $namespace Namespace to use, e.g. "App\\Tasks".
	 */
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

	/**
	 * Check whether automatic parameter type coercion is enabled.
	 *
	 * When enabled, string arguments that look like integers, floats, booleans,
	 * or NULL are converted to the corresponding PHP scalar before being passed
	 * to the action method.
	 *
	 * @return bool True if parameter parsing is enabled.
	 */
	public function shouldParseParams(): bool
	{
		return $this->parseParams;
	}

	/**
	 * Enable or disable automatic parameter type coercion.
	 *
	 * @param bool $parseParams True to enable coercion, false to pass all arguments as strings.
	 */
	public function setParseParams(bool $parseParams): void
	{
		$this->parseParams = $parseParams;
	}

	/**
	 * Resolve and invoke a task action.
	 *
	 * Converts the task and action names to CamelCase, prepends the configured
	 * namespace, instantiates the task class, optionally coerces the parameters,
	 * and calls the action method.
	 *
	 * @param string|null $task   Task name as passed on the command line (e.g. "my-task"). Null falls back to the default task.
	 * @param string|null $action Action name as passed on the command line (e.g. "run"). Null falls back to the default action.
	 * @param array       $params Remaining command-line arguments passed as positional parameters to the action.
	 * @return mixed The return value of the invoked action method.
	 * @throws TaskNotFoundException   If the resolved task class does not exist.
	 * @throws InvalidTaskException    If the resolved class is not a subclass of {@see Task}.
	 * @throws ActionNotFoundException If the resolved method does not exist on the task.
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
