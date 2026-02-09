<?php /** @noinspection PhpUnused */

namespace CoreLib\Http;

/**
 * HTTP Request class
 */
class Request
{
	/**
	 * Extract JSON post data into $_POST and $_REQUEST variables
	 */
	public function extractJsonPostData()
	{
		if (isset($_SERVER['CONTENT_TYPE']) && strcasecmp($_SERVER['CONTENT_TYPE'], 'application/json') == 0) {
			$json = @json_decode(file_get_contents('php://input'), true);
			if (is_array($json)) {
				foreach ($json as $key => $value) {
					$_POST[$key] = $value;
					$_REQUEST[$key] = $value;
				}
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function parseJsonPostData()
	{
		return @json_decode(file_get_contents('php://input'), true);
	}

	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function get($name = null, $defaultValue = null)
	{
		return isset($name) ? (isset($_REQUEST[$name]) ? $_REQUEST[$name] : $defaultValue) : $_REQUEST;
	}

	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getPost($name = null, $defaultValue = null)
	{
		return isset($name) ? (isset($_POST[$name]) ? $_POST[$name] : $defaultValue) : $_POST;
	}

	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getQuery($name = null, $defaultValue = null)
	{
		return isset($name) ? (isset($_GET[$name]) ? $_GET[$name] : $defaultValue) : $_GET;
	}

	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getServer($name = null, $defaultValue = null)
	{
		return isset($name) ? (isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultValue) : $_SERVER;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		$requestMethod = 'GET';
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$requestMethod = $_SERVER['REQUEST_METHOD'];
		}

		if ($requestMethod === 'POST') {
			if (isset($_SERVER['X_HTTP_METHOD_OVERRIDE'])) {
				$requestMethod = strtoupper($_SERVER['X_HTTP_METHOD_OVERRIDE']);
			}
		}

		return $requestMethod;
	}

	/**
	 * @return string
	 */
	public function getScheme()
	{
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
	}

	/**
	 * @return string
	 */
	public function getServerName()
	{
		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
	}

	/**
	 * @return string
	 */
	public function getServerAddr()
	{
		return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbyname('localhost');
	}

	/**
	 * @return string
	 */
	public function getHttpHost()
	{
		if (!empty($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		}
		if (!empty($_SERVER['SERVER_NAME'])) {
			return $_SERVER['SERVER_NAME'];
		}
		if (!empty($_SERVER['SERVER_ADDR'])) {
			return $_SERVER['SERVER_ADDR'];
		}
		return '';
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		if (!empty($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
			$index = strpos($host, ':');
			if ($index !== false) {
				return (int) substr($host, $index + 1);
			}
			return $this->isSecure() ? 443 : 80;
		}
		return isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		if (isset($_SERVER['CONTENT_TYPE'])) {
			return $_SERVER['CONTENT_TYPE'];
		}
		if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
			// see https://bugs.php.net/bug.php?id=66606
			return $_SERVER['HTTP_CONTENT_TYPE'];
		}
		return '';
	}

	/**
	 * @param bool $trustForwardedHeader
	 * @return string|bool
	 */
	public function getClientAddress($trustForwardedHeader = false)
	{
		// return IP given by proxy?

		if ($trustForwardedHeader) {
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$address = $_SERVER['HTTP_CLIENT_IP'];
			}
		}

		if (empty($address)) {
			$address = $_SERVER["REMOTE_ADDR"];
		}

		if (!isset($address)) {
			return false;
		}

		if (strpos($address, ",") !== false) {
			// client address has multiples parts, return only first part
			return explode(",", $address)[0];
		}

		return $address;
	}

	/**
	 * @return string
	 */
	public function getUri()
	{
		return $_SERVER['REQUEST_URI'] ?? '';
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	/**
	 * @param $serverIndex
	 * @param $name
	 * @param $sort
	 * @return array
	 */
	protected final function _getQualityHeader($serverIndex, $name, $sort)
	{
		// Accept: text/html, application/xhtml+xml, application/xml;q=0.9, image/webp, */*;q=0.8
		// Accept-Language: fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5
		$returnedParts = [];
		$lastQuality = 1;
		$needSort = false;
		if (isset($_SERVER[$serverIndex])) {
			foreach (explode(',', $_SERVER[$serverIndex]) as $item) {
				$headerParts = [];
				foreach (explode(';', $item) as $part) {
					$part = trim($part);
					$index = strpos($part, '=');
					if ($index === false) {
						$headerParts[$name] = $part;
						$headerParts['quality'] = 1.0;
					} elseif ($index === 1 && $part[0] === 'q') {
						$quality = (float) substr($part, 2);
						$headerParts['quality'] = $quality;
						$needSort = $quality > $lastQuality;
						$lastQuality = $quality;
					} else {
						$headerParts[substr($part, 0, $index)] = substr($part, $index + 1);
					}
				}
				$returnedParts[] = $headerParts;
			}
		}
		if ($sort && $needSort) {
			usort($returnedParts, function ($a, $b) {
				return (int) ($b['quality'] * 100) <=> (int) ($a['quality'] * 100);
			});
		}
		return $returnedParts;
	}

	/**
	 * Gets an array with mime/types and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT"]
	 * @return array
	 */
	public function getAcceptableContent($sort = false)
	{
		return $this->_getQualityHeader('HTTP_ACCEPT', 'accept', $sort);
	}

	/**
	 * Gets best mime/type accepted by the browser/client from _SERVER["HTTP_ACCEPT"]
	 * @return string
	 */
	public function getBestAccept()
	{
		return $this->getAcceptableContent(true)[0]['accept'] ?? '';
	}

	/**
	 * Gets a charsets array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]
	 * @return array
	 */
	public function getClientCharsets($sort = false)
	{
		return $this->_getQualityHeader("HTTP_ACCEPT_CHARSET", 'charset', $sort);
	}

	/**
	 * Gets best charset accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]
	 * @return string
	 */
	public function getBestCharset()
	{
		return $this->getClientCharsets(true)[0]['charset'] ?? '';
	}

	/**
	 * Gets languages array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]
	 */
	public function getLanguages($sort = false)
	{
		return $this->_getQualityHeader("HTTP_ACCEPT_LANGUAGE", 'language', $sort);
	}

	/**
	 * Gets best language accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]
	 */
	public function getBestLanguage()
	{
		return $this->getLanguages(true)[0]['language'] ?? '';
	}

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']
	 * @return array|null
	 */
	public function getBasicAuth()
	{
		if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
			return [
				'username' => $_SERVER["PHP_AUTH_USER"],
				'password' => $_SERVER["PHP_AUTH_PW"],
			];
		}
		return null;
	}

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']
	 * @return array|null
	 */
	public function getDigestAuth()
	{
		if (isset($_SERVER["PHP_AUTH_DIGEST"])) {
			if (preg_match_all("#(\\w+)=(['\"]?)([^'\" ,]+)\\2#", $_SERVER["PHP_AUTH_DIGEST"], $matches, 2)) {
				$auth = [];
				foreach ($matches as $match) {
					$auth[$match[1]] = $match[3];
				}
				return $auth;
			}
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	 * Checks whether request has been made using SOAP
	 * @return bool
	 */
	public function isSoap()
	{
		if (isset($_SERVER["HTTP_SOAPACTION"])) {
			return true;
		} else {
			return $this->getContentType() == 'application/soap+xml';
		}
	}

	/**
	 * @return bool
	 */
	public function isSecure()
	{
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	}

	/**
	 * @return bool
	 */
	public function isPost()
	{
		return $this->getMethod() === 'POST';
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return isset($_REQUEST[$name]);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPost($name)
	{
		return isset($_POST[$name]);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasQuery($name)
	{
		return isset($_GET[$name]);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasServer($name)
	{
		return isset($_SERVER[$name]);
	}
}