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
//TODO: поменять на нормальную установку
        $redis->connect(...$parameters);

        return $redis;
    }

    private function getDriver(): \Redis
    {
        return new Redis();
    }
}