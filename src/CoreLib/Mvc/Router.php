<?php

namespace CoreLib\Mvc;

//useLanguage CoreLib\Mvc\RouteNotFoundException;
use CoreLib\Exception;

/**
 * Provides routing capabilities to an application
 */
class Router
{

	public string $defaultController = "IndexController";

	public string $defaultAction = "indexAction";

	public bool $parseParams = false;

	/**
	 * @var array
	 */
	private $_routes = [];

	/**
	 * @var array
	 */
	private $_matches;

	/**
	 * @var array
	 */
	private $_parts;

	/**
	 * @var array
	 */
	private $_params;

	/**
	 * @var string
	 */
	private $_controllerName;

	/**
	 * @var string
	 */
	private $_actionName;

	/**
	 * @var string[]
	 */
	private $patternPlaceHolder = [
		'/:namespace',
		'/:controller',
		'/:action',
		'/:params',
		'/:int',
	];

	/**
	 * @var string[]
	 */
	private $patternReplacement = [
		'/([\w-]+)',
		'/([\w-]+)',
		'/([\w-]+)',
		'(/.*)?',
		'/([0-9]+)',
	];

	/**
	 * @param string $name
	 * @param string $pattern
	 */
	public function addPlaceHolder(string $name, string $pattern)
	{
		$this->patternPlaceHolder[] = $name;
		$this->patternReplacement[] = $pattern;
	}

	/**
	 * @param string $pattern
	 * @param array|string $paths
	 * @param string|array $httpMethods
	 * @param bool $firstPosition
	 * @return $this
	 */
	public function add($pattern, $paths, $httpMethods = null, $firstPosition = false)
	{
		// convert paths to array
		if (is_string($paths)) {
			list($controller, $action) = explode('::', $paths);
			$paths = [
				'controller' => $controller,
				'action' => $action,
			];
		}
		// convert placeholders
		$pattern = str_replace($this->patternPlaceHolder, $this->patternReplacement, $pattern);
		// convert named parameters
		$pattern = mb_ereg_replace_callback('{([A-Za-z_]\w+)(:([^{}]+({[^}]+})?))?}', function ($match) use (&$paths) {
			$paths[$match[1]] = $match[1];
			return empty($match[3]) ? "(?<{$match[1]}>.+?)" : "(?<{$match[1]}>$match[3])";
		}, $pattern);
		// add route
		$route = [
			'pattern' => '^' . $pattern . '$',
			'paths' => $paths,
			'methods' => $httpMethods,
		];
		//var_dump($route);
		if ($firstPosition) {
			array_unshift($this->_routes, $route);
		} else {
			$this->_routes[] = $route;
		}
		return $this;
	}

	/**
	 * @param string $pattern
	 * @param array|string $paths
	 * @param bool $firstPosition
	 * @return $this
	 */
	public function addGet($pattern, $paths, $firstPosition = false)
	{
		return $this->add($pattern, $paths, 'GET', $firstPosition);
	}

	/**
	 * @param string $pattern
	 * @param array|string $paths
	 * @param bool $firstPosition
	 * @return $this
	 */
	public function addPost($pattern, $paths, $firstPosition = false)
	{
		return $this->add($pattern, $paths, 'POST', $firstPosition);
	}

	/**
	 * @param string $uri
	 * @return bool
	 * @throws Exception
	 */
	public function handle($uri)
	{

		$uri = '/' . trim($uri, '/');
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		for ($route = end($this->_routes); key($this->_routes) !== null; $route = prev($this->_routes)) {

			// check request method
			if (!empty($route['methods'])) {
				if (is_array($route['methods'])) {
					$found = false;
					foreach ($route['methods'] as $method) {
						if (strcasecmp($requestMethod, $method) == 0) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						continue;
					}
				} else {
					if (strcasecmp($requestMethod, $route['methods']) != 0) {
						continue;
					}
				}
			}

			// match route
			if (!mb_ereg($route['pattern'], $uri, $matches)) {
				continue;
			}

			// extract path parts
			$paths = $route['paths'];
			$parts = [];
			foreach ($paths as $name => $value) {
				if (isset($matches[$value])) {
					$parts[$name] = $matches[$value];
				} else {
					$parts[$name] = $value;
				}
			}
			$this->_matches = $matches;

			if (!empty($parts['controller']) && !is_int($parts['controller'])) {
				if (isset($matches[$paths['controller']])) {
					// dynamic controller
					$controller = self::capitalizeClass($parts['controller']) . 'Controller';
				} else {
					// static controller
					$controller = $parts['controller'];
				}
			} else {
				$controller = $this->defaultController;
				$parts['controller'] = '';
			}

			if (!empty($parts['namespace']) && !is_int($parts['namespace'])) {
				if (isset($matches[$paths['namespace']])) {
					// dynamic namespace
					$controller = str_replace('.', '\\', self::capitalizeClass($parts['namespace'])) . '\\' . $controller;
				} else {
					// static namespace
					$controller = $parts['namespace'] . '\\' . $controller;
				}
			} else {
				$parts['namespace'] = '';
			}

			if (!empty($parts['action']) && !is_int($parts['action'])) {
				if (isset($matches[$paths['action']])) {
					// dynamic action
					$action = self::capitalizeMethod($parts['action']) . 'Action';
				} else {
					// static action
					$action = $parts['action'];
				}
			} else {
				$action = $this->defaultAction;
				$parts['action'] = '';
			}

			if (!empty($parts['params']) && !is_int($parts['params'])) {
				$parts['params'] = explode('/', trim($parts['params'], '/'));
			} else {
				$parts['params'] = [];
			}
			$params = $parts['params'];

			$this->_parts = $parts;
			unset($parts['namespace'], $parts['controller'], $parts['action'], $parts['params']);

			$this->_controllerName = $controller;
			$this->_actionName = $action;
			$this->_params = array_merge($params, $parts);

			if ($this->parseParams) {
				foreach ($params as $index => $param) {
					if (ctype_digit($param)) {
						$params[$index] = (int) $param;
						continue;
					}
					if (mb_ereg('^\d*(\.\d+)?$', $param)) {
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

			//var_dump($this->_parts);
			//exit;
			if (!class_exists($controller)) {
				throw new Exception("Controller {$controller} not found");
			}
			$classInstance = new $controller();
			if (!$classInstance instanceof Controller) {
				throw new Exception("Class {$controller} not an instance of \Core\Mvc\Controller");
			}
			if (!method_exists($classInstance, $action)) {
				throw new Exception("Action {$controller}->{$action}() not found");
			}

			switch (count($params)) {
				case 0:
					$classInstance->$action();
					break;
				case 1:
					$classInstance->$action($params[0]);
					break;
				case 2:
					$classInstance->$action($params[0], $params[1]);
					break;
				case 3:
					$classInstance->$action($params[0], $params[1], $params[2]);
					break;
				case 4:
					$classInstance->$action($params[0], $params[1], $params[2], $params[3]);
					break;
				case 5:
					$classInstance->$action($params[0], $params[1], $params[2], $params[3], $params[4]);
					break;
				default:
					call_user_func_array([$classInstance, $action], $params);
					break;
			}

			return true;
		}
		//throw new RouteNotFound($uri);
		return false;
	}

	/**
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->_controllerName;
	}

	/**
	 * @return string
	 */
	public function getActionName()
	{
		return $this->_actionName;
	}

	/**
	 * @param string|int $key
	 * @param string $defaultValue
	 * @return string
	 */
	public function getParam($key, $defaultValue = null)
	{
		return isset($this->_params[$key]) ? $this->_params[$key] : $defaultValue;
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * @return array
	 */
	public function getMatches()
	{
		return $this->_matches;
	}

	/**
	 * @return array
	 */
	public function getParts()
	{
		return $this->_parts;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function capitalizeClass($string)
	{
		// BulkSell -> BulkSell
		// bulkSell -> BulkSell
		// bulk-sell -> BulkSell
		// bulksell -> Bulksell
		if (strpos($string, '-') !== false) {
			$result = '';
			foreach (explode('-', $string) as $word) {
				if (!empty($word)) {
					$result .= strtoupper($word[0]) . strtolower(substr($word, 1));
				}
			}
			return $result;
		}
		return strtoupper($string[0]) . substr($string, 1);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function capitalizeMethod($string)
	{
		// BulkSell -> bulkSell
		// bulkSell -> bulkSell
		// bulk-sell -> bulkSell
		// bulksell -> bulksell
		if (strpos($string, '-') !== false) {
			$result = '';
			foreach (explode('-', $string) as $index => $word) {
				if (!empty($word)) {
					if ($index > 0) {
						$result .= strtoupper($word[0]) . strtolower(substr($word, 1));
					} else {
						$result .= strtolower($word);
					}
				}
			}
			return $result;
		}
		return strtolower($string[0]) . substr($string, 1);
	}
}
