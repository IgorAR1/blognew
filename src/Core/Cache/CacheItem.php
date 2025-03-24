<?php

namespace App\Core\Cache;

use App\Core\Arrayable;
use Psr\Cache\CacheItemInterface;

final class CacheItem implements CacheItemInterface, Arrayable
{

    //items => [
    //[
    // 'key' => 'someKey',
    // 'value' => 'someValue',
    // 'ttl' => 3600
    //]
    //]

    private ?\DateTimeInterface $expiration = null;
    private int|null|\DateInterval $time = null;

    public function __construct(readonly string $key,
                                private ?string $value = null,
                                private bool    $isHit = false)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function toArray(): array
    {
      return [
          'key' => $this->key,
          'value' => $this->value,
          'isHit' => $this->isHit,
          'expiration' => $this->expiration,
          'time' => $this->time,
      ];
    }
}