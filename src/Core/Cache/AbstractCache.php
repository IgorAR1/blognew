<?php

namespace App\Core\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractCache implements CacheItemPoolInterface
{
    protected function createCacheItem(string $key, ?string $value, bool $isHit): CacheItemInterface
    {
        return new CacheItem($key, $value, $isHit);
    }
}