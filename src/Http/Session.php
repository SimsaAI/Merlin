<?php
namespace Merlin\Http;

class Session
{
    public function __construct(private array &$store)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }

    public function has(string $key): bool
    {
        return isset($this->store[$key]);
    }

    public function clear(): void
    {
        $this->store = [];
    }
}
