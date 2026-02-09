<?php
namespace CoreLib\Mvc;

use CoreLib\Exception;

/**
 * View class
 */
class View
{

	/**
	 * @var array
	 */
	private $_vars = [];

	/**
	 * @var View
	 */
	private $view;

	/**
	 * @var string
	 */
	private $path = __DIR__ . '/../../../../views';

	/**
	 * @var int
	 */
	private $renderDepth = 0;

	/**
	 * View constructor.
	 */
	public function __construct()
	{
		$this->view = $this;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setVar($name, $value)
	{
		$this->_vars[$name] = $value;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param string $view
	 * @param array $vars
	 * @return bool
	 * @throws Exception
	 */
	public function render($view, $vars = null)
	{
		$this->renderDepth++;
		$path = $this->path . '/' . $view . '.phtml';
		if (!file_exists($path)) {
			throw new Exception('No view found with name "' . $view . '"');
		}
		extract(array_merge($this->_vars, $vars ?: []));
		require $path;
		$this->renderDepth--;
		return true;
	}

	/**
	 * @param string $view
	 * @param int $level
	 * @param array $vars
	 * @return bool
	 * @throws Exception
	 */
	public function renderTop($view, $level = 1, $vars = null)
	{
		if ($this->renderDepth <= $level) {
			return $this->render($view, $vars);
		}
		return true;
	}
}
