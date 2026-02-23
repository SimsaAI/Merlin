<?php

namespace Merlin\Http;

use Merlin\Crypt;
use Merlin\Exception;

/**
 * Represents a single HTTP cookie with optional transparent encryption.
 *
 * Use the static {@see make()} factory or construct directly, then call
 * {@see send()} to emit the Set-Cookie header. Read the cookie value with
 * {@see value()}, which handles decryption automatically.
 */
class Cookie
{
	protected string $name;
	protected mixed $value = null;
	protected bool $loaded = false;

	protected int $expires = 0;
	protected string $path = '/';
	protected string $domain = '';
	protected bool $secure = false;
	protected bool $httpOnly = true;

	protected bool $encrypted = false;
	protected string $cipher = Crypt::CIPHER_AUTO;
	protected ?string $key = null;

	// --- Factory Methods -----------------------------------------------------

	/**
	 * Create a new Cookie instance with the given parameters.
	 *
	 * @param string $name The name of the cookie.
	 * @param mixed $value The value of the cookie (optional).
	 * @param int $expires Expiration timestamp (optional).
	 * @param string $path Path for which the cookie is valid (optional).
	 * @param string $domain Domain for which the cookie is valid (optional).
	 * @param bool $secure Whether the cookie should only be sent over HTTPS (optional).
	 * @param bool $httpOnly Whether the cookie should be inaccessible to JavaScript (optional).
	 * @return static A new Cookie instance.
	 */
	public static function make(
		string $name,
		mixed $value = null,
		int $expires = 0,
		string $path = '/',
		string $domain = '',
		bool $secure = false,
		bool $httpOnly = true
	): static {
		return new static($name, $value, $expires, $path, $domain, $secure, $httpOnly);
	}

	// --- Constructor ---------------------------------------------------------

	/**
	 * Create a new Cookie instance.
	 *
	 * @param string $name     Cookie name.
	 * @param mixed  $value    Initial value (null means "not yet loaded").
	 * @param int    $expires  Expiration timestamp (0 = session cookie).
	 * @param string $path     URL path scope.
	 * @param string $domain   Domain scope.
	 * @param bool   $secure   Send over HTTPS only.
	 * @param bool   $httpOnly Inaccessible to JavaScript.
	 */
	public function __construct(
		string $name,
		mixed $value = null,
		int $expires = 0,
		string $path = '/',
		string $domain = '',
		bool $secure = false,
		bool $httpOnly = true
	) {
		$this->name = $name;

		if ($value !== null) {
			$this->value = $value;
			$this->loaded = true;
		}

		$this->expires = $expires;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->httpOnly = $httpOnly;
	}

	// --- Value Handling ------------------------------------------------------

	/**
	 * Read the cookie value, lazily loading it from $_COOKIE and decrypting if needed.
	 *
	 * @param mixed $default Value to return when the cookie is not present.
	 * @return mixed
	 */
	public function value(mixed $default = null): mixed
	{
		if ($this->loaded) {
			return $this->value;
		}

		if (!isset($_COOKIE[$this->name])) {
			return $default;
		}

		$raw = $_COOKIE[$this->name];

		if ($this->encrypted) {
			$raw = $this->decrypt($raw);
		}

		$this->value = $raw;
		$this->loaded = true;

		return $this->value;
	}

	/**
	 * Set the cookie value (in memory; call {@see send()} to persist).
	 *
	 * @param mixed $value New value.
	 * @return $this
	 */
	public function set(mixed $value): static
	{
		$this->value = $value;
		$this->loaded = true;
		return $this;
	}

	// --- Sending -------------------------------------------------------------

	/**
	 * Emit a Set-Cookie header with the current cookie configuration.
	 *
	 * Encrypts the value first if encryption is enabled.
	 *
	 * @return $this
	 */
	public function send(): static
	{
		$value = $this->value;

		if ($this->encrypted && $value !== null) {
			$value = $this->encrypt($value);
		}

		setcookie(
			$this->name,
			$value,
			$this->expires,
			$this->path,
			$this->domain,
			$this->secure,
			$this->httpOnly
		);

		return $this;
	}

	/**
	 * Delete the cookie by setting its expiration to the past.
	 */
	public function delete(): void
	{
		setcookie(
			$this->name,
			'',
			time() - 3600,
			$this->path,
			$this->domain,
			$this->secure,
			$this->httpOnly
		);
	}

	// --- Encryption ----------------------------------------------------------

	/**
	 * Enable or disable transparent encryption for this cookie.
	 *
	 * @param bool $state True to enable encryption (default), false to disable.
	 * @return $this
	 */
	public function encrypted(bool $state = true): static
	{
		$this->encrypted = $state;
		return $this;
	}

	/**
	 * Set the encryption cipher to use (one of the {@see \Merlin\Crypt}::CIPHER_* constants).
	 *
	 * @param string $cipher Cipher identifier.
	 * @return $this
	 */
	public function cipher(string $cipher): static
	{
		$this->cipher = $cipher;
		return $this;
	}

	/**
	 * Set the encryption key. Defaults to a key derived from PHP's uname when null.
	 *
	 * @param string|null $key Encryption key or null to use the default key.
	 * @return $this
	 */
	public function key(?string $key): static
	{
		$this->key = $key;
		return $this;
	}

	protected function encrypt(string $value): string
	{
		return Crypt::encrypt($value, $this->resolveKey(), $this->cipher);
	}

	protected function decrypt(string $value): mixed
	{
		return Crypt::decrypt($value, $this->resolveKey(), $this->cipher);
	}

	protected function resolveKey(): string
	{
		if ($this->key !== null) {
			return $this->key;
		}

		return hash('sha256', php_uname(), true);
	}

	// --- Metadata ------------------------------------------------------------

	/**
	 * Get the cookie name.
	 *
	 * @return string Cookie name.
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Set the expiration timestamp.
	 *
	 * @param int $timestamp Unix timestamp (0 = session cookie).
	 * @return $this
	 */
	public function expires(int $timestamp): static
	{
		$this->expires = $timestamp;
		return $this;
	}

	/**
	 * Set the URL path scope for the cookie.
	 *
	 * @param string $path URL path (e.g. "/").
	 * @return $this
	 */
	public function path(string $path): static
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * Set the domain scope for the cookie.
	 *
	 * @param string $domain Domain (e.g. ".example.com").
	 * @return $this
	 */
	public function domain(string $domain): static
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Restrict the cookie to HTTPS connections only.
	 *
	 * @param bool $state True to require HTTPS.
	 * @return $this
	 */
	public function secure(bool $state): static
	{
		$this->secure = $state;
		return $this;
	}

	/**
	 * Make the cookie inaccessible to JavaScript (HttpOnly flag).
	 *
	 * @param bool $state True to set the HttpOnly flag.
	 * @return $this
	 */
	public function httpOnly(bool $state): static
	{
		$this->httpOnly = $state;
		return $this;
	}

	/**
	 * Return the cookie value as a string (useful for string-casting).
	 *
	 * @return string Cookie value, or empty string when not set.
	 */
	public function __toString(): string
	{
		return (string) $this->value();
	}
}
