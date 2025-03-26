<?php

namespace App\Core\Config;

class Config
{
    public function __construct(private array $parameters = [])
    {}

    public function get(string $key = ''): array|string|null
    {
        if ($key === '') {
            return $this->parameters;
        }

        $keys = explode('.', $key);
        $value = $this->parameters;

        foreach ($keys as $k) {
            $value = $value[$k] ?? null;

        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$this->parameters;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }
}