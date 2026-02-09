<?php

namespace CoreLib\Http;

use CoreLib\Crypt;
use CoreLib\Exception;

/**
 * HTTP Cookie class
 */
class Cookie
{
	protected $_read = false;

	protected $_name;

	protected $_value = '';

	protected $_expire = 0;

	protected $_path = '/';

	protected $_domain = '';

	protected $_secure = false;

	protected $_httpOnly = true;

	protected $_useEncryption = false;

	protected $_encryptionCipher = Crypt::CIPHER_AUTO;

	protected $_encryptionKey = null;

	/**
	 * @param string name
	 * @param mixed value
	 * @param int expire
	 * @param string path
	 * @param boolean secure
	 * @param string domain
	 * @param boolean httpOnly
	 */
	public function __construct($name, $value = null, $expire = 0, $path = '/', $secure = false, $domain = '', $httpOnly = true)
	{
		$this->_name = $name;

		if (isset($value)) {
			$this->setValue($value);
		}

		$this->_expire = $expire;

		if (isset($path)) {
			$this->_path = $path;
		}

		if (isset($secure)) {
			$this->_secure = $secure;
		}

		if (isset($domain)) {
			$this->_domain = $domain;
		}

		if (isset($httpOnly)) {
			$this->_httpOnly = $httpOnly;
		}
	}

	/**
	 * Sets the cookie's value
	 *
	 * @param string $value
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		$this->_read = true;
		return $this;
	}

	/**
	 * Returns the cookie's value
	 *
	 * @param string $defaultValue
	 * @return mixed
	 * @throws Exception
	 */
	public function getValue($defaultValue = null)
	{
		if (!$this->_read) {

			if (isset($_COOKIE[$this->_name])) {
				$value = $_COOKIE[$this->_name];

				if ($this->_useEncryption) {

					/**
					 * Decrypt the value also decoding it with base64
					 */
					$decryptedValue = $this->_decryptCookieValue($value);

				} else {
					$decryptedValue = $value;
				}

				/**
				 * Update the decrypted value
				 */
				$this->_value = $decryptedValue;

				/**
				 * Return the value without filtering
				 */
				return $decryptedValue;
			}

			return $defaultValue;
		}

		return $this->_value;
	}

	/**
	 * Sends the cookie to the HTTP client
	 * Stores the cookie definition in session
	 * @return $this
	 * @throws Exception
	 */
	public function send()
	{
		$value = $this->_value;

		if ($this->_useEncryption) {

			if (!empty($value)) {

				/**
				 * Encrypt the value also coding it with base64
				 */
				$encryptValue = $this->_encryptCookieValue($value);

			} else {
				$encryptValue = $value;
			}

		} else {
			$encryptValue = $value;
		}

		/**
		 * Sets the cookie using the standard 'setcookie' function
		 */
		setcookie($this->_name, $encryptValue, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_httpOnly);

		return $this;
	}

	/**
	 * Deletes the cookie by setting an expire time in the past
	 */
	public function delete()
	{

		$this->_value = null;

		setcookie($this->_name, null, time() - 691200, $this->_path, $this->_domain, $this->_secure, $this->_httpOnly);
	}

	/**
	 * Sets if the cookie must be encrypted/decrypted automatically
	 * @param bool $useEncryption
	 * @return $this
	 */
	public function useEncryption($useEncryption)
	{
		$this->_useEncryption = $useEncryption;
		return $this;
	}

	/**
	 * Check if the cookie is using implicit encryption
	 */
	public function isUsingEncryption()
	{
		return $this->_useEncryption;
	}

	/**
	 * Sets the cookie's expiration time
	 * @param int $expire
	 * @return $this
	 */
	public function setExpiration($expire)
	{
		$this->_expire = $expire;
		return $this;
	}

	/**
	 * Returns the current expiration time
	 */
	public function getExpiration()
	{
		return $this->_expire;
	}

	/**
	 * Sets the cookie's expiration time
	 * @param string $path
	 * @return $this
	 */
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}

	/**
	 * Returns the current cookie's name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Returns the current cookie's path
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Sets the domain that the cookie is available to
	 * @param string $domain
	 * @return $this
	 */
	public function setDomain($domain)
	{
		$this->_domain = $domain;
		return $this;
	}

	/**
	 * Returns the domain that the cookie is available to
	 */
	public function getDomain()
	{
		return $this->_domain;
	}

	/**
	 * Sets if the cookie must only be sent when the connection is secure (HTTPS)
	 * @param bool $secure
	 * @return $this
	 */
	public function setSecure($secure)
	{
		$this->_secure = $secure;
		return $this;
	}

	/**
	 * Returns whether the cookie must only be sent when the connection is secure (HTTPS)
	 */
	public function getSecure()
	{
		return $this->_secure;
	}

	/**
	 * Sets if the cookie is accessible only through the HTTP protocol
	 * @param bool $httpOnly
	 * @return $this
	 */
	public function setHttpOnly($httpOnly)
	{
		$this->_httpOnly = $httpOnly;
		return $this;
	}

	/**
	 * Returns if the cookie is accessible only through the HTTP protocol
	 */
	public function getHttpOnly()
	{
		return $this->_httpOnly;
	}

	/**
	 * Magic __toString method converts the cookie's value to string
	 * @throws Exception
	 */
	public function __toString()
	{
		return (string) $this->getValue();
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setEncryptionKey($key)
	{
		$this->_encryptionKey = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEncryptionKey()
	{
		static $key = null;
		if (!isset($this->_encryptionKey)) {
			if (!isset($key)) {
				// Import note: Key changes after system update
				$key = hash('sha256', php_uname(), true);
			}
			$this->_encryptionKey = $key;
		}
		return $this->_encryptionKey;
	}

	/**
	 * Sets the encryption cipher
	 *
	 * @param string $cipher Use Crypt::CIPHER_CHACHA20_POLY1305, Crypt::CIPHER_AES_256_GCM, or Crypt::CIPHER_AUTO
	 * @return $this
	 */
	public function setEncryptionCipher($cipher)
	{
		$this->_encryptionCipher = $cipher;
		return $this;
	}

	/**
	 * Gets the current encryption cipher
	 *
	 * @return string
	 */
	public function getEncryptionCipher()
	{
		return $this->_encryptionCipher;
	}

	/**
	 * @param string $value
	 * @return string
	 * @throws Exception
	 */
	protected function _encryptCookieValue($value)
	{
		$key = $this->getEncryptionKey();
		return Crypt::encrypt($value, $key, $this->_encryptionCipher);
	}

	/**
	 * @param string $value
	 * @return mixed|null
	 * @throws Exception
	 */
	protected function _decryptCookieValue($value)
	{
		$key = $this->getEncryptionKey();
		return Crypt::decrypt($value, $key, $this->_encryptionCipher);
	}
}