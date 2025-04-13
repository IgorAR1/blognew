<?php

namespace App\Core\Cache\Redis;


use App\Core\Config\Config;
use Redis;

class RedisConnector
{
    public function __construct(readonly array $config)
    {}

    public function connection(): Redis
    {
        return $this->connect($this->config);
    }
    public function connect(array $parameters): \Redis
    {
        $redis = $this->getDriver();
        ///тут типа валидация параметров - варианта два - либо сделать класс CacheConfig и юзать его как дто либо валидировать где здесь в отдельном методе
//TODO: поменять на нормальную установку
        $redis->connect(...$parameters);

        return $redis;
    }

    private function getDriver(): \Redis
    {
        return new Redis();
    }
}