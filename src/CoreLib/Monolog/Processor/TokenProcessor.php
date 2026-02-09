<?php

namespace CoreLib\Monolog\Processor;

use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

/**
 * Token processor
 */
class TokenProcessor implements ProcessorInterface
{

	const ANSI_BLACK = 0;
	const ANSI_RED = 1;
	const ANSI_GREEN = 2;
	const ANSI_YELLOW = 3;
	const ANSI_BLUE = 4;
	const ANSI_PURPLE = 5;
	const ANSI_CYAN = 6;
	const ANSI_WHITE = 7;

	/**
	 * @var string
	 */
	private static $reset_color = "\033[0m";

	/**
	 * @var array
	 */
	private static $skipFunctionMap = [
		'call_user_func' => true,
		'call_user_func_array' => true,
	];

	public static function addSkipFunction($name)
	{
		foreach (func_get_args() as $arg) {
			self::$skipFunctionMap[$arg] = true;
		}
	}

	/**
	 * @var array
	 */
	private static $skipClassMap = [
	];

	public static function addSkipClass($name)
	{
		foreach (func_get_args() as $arg) {
			self::$skipClassMap[$arg] = true;
		}
	}

	/**
	 * @param int $fore
	 * @param int $back
	 * @return string
	 */
	private static function getNormalColor($fore, $back = -1)
	{
		$code = "\033[3";
		$code .= $fore;
		if ($back >= self::ANSI_BLACK && $back <= self::ANSI_WHITE) {
			$code .= ';4';
			$code .= $back;
		}
		$code .= 'm';
		return $code;
	}

	/**
	 * @param int $fore
	 * @param int $back
	 * @return string
	 */
	private static function getBoldColor($fore, $back = -1)
	{
		$code = "\033[9";
		$code .= $fore;
		if ($back >= self::ANSI_BLACK && $back <= self::ANSI_WHITE) {
			$code .= ';4';
			$code .= $back;
		}
		$code .= 'm';
		return $code;
	}

	/**
	 * @var array
	 */
	private $level_colors = null;

	/**
	 * @var string
	 */
	private $token = '-';

	/**
	 * TokenProcessor constructor.
	 */
	public function __construct()
	{
		//$this->generateToken();
		$this->level_colors = [
			Logger::EMERGENCY => self::getBoldColor(self::ANSI_WHITE, self::ANSI_RED),
			Logger::ALERT => self::getBoldColor(self::ANSI_WHITE, self::ANSI_RED),
			Logger::CRITICAL => self::getBoldColor(self::ANSI_WHITE, self::ANSI_RED),
			Logger::ERROR => self::getBoldColor(self::ANSI_RED),
			Logger::WARNING => self::getBoldColor(self::ANSI_YELLOW),
			Logger::NOTICE => self::getBoldColor(self::ANSI_GREEN),
			Logger::INFO => self::getBoldColor(self::ANSI_WHITE),
			Logger::DEBUG => self::getNormalColor(self::ANSI_WHITE),
		];
	}

	/**
	 * @return $this
	 */
	public function generateToken()
	{
		$this->token = hash('crc32b', getmypid() . microtime(true));
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return $this
	 */
	public function setToken($token)
	{
		$this->token = (string) $token;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function resetToken()
	{
		$this->token = '-';
		return $this;
	}

	/**
	 * @param array $record
	 * @return array The processed record
	 */
	public function __invoke(array $record)
	{

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);

		for ($index = 4; ; ) {
			if (isset($trace[$index]['class'])) {
				if (!empty(self::$skipClassMap[$trace[$index]['class']])) {
					$index++;
					continue;
				}
				break;
			}
			if (isset($trace[$index]['function']) && !empty(self::$skipFunctionMap[$trace[$index]['function']])) {
				$index++;
				continue;
			}
			break;
		}

		// we should have the call source now
		if (isset($trace[$index - 1]['file'])) {
			$file = basename($trace[$index - 1]['file']) . ':' . $trace[$index - 1]['line'];
		} else {
			$file = '*system code*';
		}

		if (isset($trace[$index]['class'])) {

			$class = $trace[$index]['class'];
			$pos = strrpos($class, '\\');
			if ($pos !== false) {
				$class = substr($class, $pos + 1);
			}
			$function = $class . '->' . $trace[$index]['function'];

		} elseif (isset($trace[$index]['function'])) {

			$function = $trace[$index]['function'];

		} else {

			$function = '*global*';
		}

		$record['x_file'] = $file;
		$record['x_func'] = $function;
		$record['x_token'] = $this->token;
		$record['x_level'] = substr($record['level_name'], 0, 4);
		$record['x_color'] = $this->level_colors[$record['level']];
		$record['x_reset'] = self::$reset_color;

		return $record;
	}
}
