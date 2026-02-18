<?php

namespace Merlin\Http;

class Cookies
{
    /** @var array<string, Cookie> */
    protected array $cookies = [];

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->cookies[$name]->value($default)
            ?? ($_COOKIE[$name] ?? $default);
    }

    public function cookie(string $name): Cookie
    {
        return $this->cookies[$name]
            ??= new Cookie($name);
    }

    public function set(
        string $name,
        mixed $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ): Cookie {
        $cookie = new Cookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
        $this->cookies[$name] = $cookie;
        return $cookie;
    }

    public function delete(string $name): void
    {
        if (isset($this->cookies[$name])) {
            $this->cookies[$name]->delete();
        } else {
            (new Cookie($name))->delete();
        }
    }

    public function sendAll(): void
    {
        foreach ($this->cookies as $cookie) {
            $cookie->send();
        }
    }
}
