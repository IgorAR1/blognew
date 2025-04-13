<?php

namespace App\Core\Cache;

use App\Core\Cache\Redis\RedisCache;
use App\Core\Cache\Redis\RedisConnector;
use App\Core\Support\ServiceProvider\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Redis;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->application->bind(CacheItemPoolInterface::class, RedisCache::class);
        $this->application->bind(Redis::class, function (): Redis {
            $connector = new RedisConnector($this->application->config('redis'));
            return $connector->connection();
        });
    }
}