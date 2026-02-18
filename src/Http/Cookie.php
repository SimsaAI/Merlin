<?php

namespace Merlin\Http;

use Merlin\Crypt;
use Merlin\Exception;

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

	public function set(mixed $value): static
	{
		$this->value = $value;
		$this->loaded = true;
		return $this;
	}

	// --- Sending -------------------------------------------------------------

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

	public function encrypted(bool $state = true): static
	{
		$this->encrypted = $state;
		return $this;
	}

	public function cipher(string $cipher): static
	{
		$this->cipher = $cipher;
		return $this;
	}

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

	public function name(): string
	{
		return $this->name;
	}

	public function expires(int $timestamp): static
	{
		$this->expires = $timestamp;
		return $this;
	}

	public function path(string $path): static
	{
		$this->path = $path;
		return $this;
	}

	public function domain(string $domain): static
	{
		$this->domain = $domain;
		return $this;
	}

	public function secure(bool $state): static
	{
		$this->secure = $state;
		return $this;
	}

	public function httpOnly(bool $state): static
	{
		$this->httpOnly = $state;
		return $this;
	}

	public function __toString(): string
	{
		return (string) $this->value();
	}
}
