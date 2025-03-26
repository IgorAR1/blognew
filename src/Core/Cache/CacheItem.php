<?php

namespace App\Core\Cache;

use Psr\Cache\CacheItemInterface;

final class CacheItem implements CacheItemInterface
{

    //items => [
    //[
    // 'key' => 'someKey',
    // 'value' => 'someValue',
    // 'ttl' => 3600
    //]
    //]

    private int|float|null $expiration = null;

    public function __construct(readonly string $key,
                                private ?string $value = null,
                                private bool    $isHit = false)
    {}

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
        if ($expiration instanceof \DateTimeInterface) {
            $this->expiration = $expiration->getTimestamp();
        } else {
            $this->expiration = null;
        }

        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        if (null === $time) {
            $this->expiration = null;
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = (microtime(true) + (float)\DateTimeImmutable::createFromFormat('U', 0)->add($time)->format('U.u'));
        } elseif (is_integer($time)) {
            $this->expiration = ($time + microtime(true));
        }

        return $this;
    }

}