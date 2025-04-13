<?php

namespace App\Core\Cache\Redis;


use App\Core\Cache\AbstractCache;
use Psr\Cache\CacheItemInterface;
use Redis;

final class RedisCache extends AbstractCache
{
    private array $deferredItems;

    public function __construct(readonly Redis $redis)
    {}

    public function getItem(string $key): CacheItemInterface
    {
        $isHit = true;

        if (!$value = $this->redis->get($key)) {
            $value = null;
            $isHit = false;
        }

        return $this->createCacheItem($key, $value, $isHit);
    }

    public function getItems(array $keys = []): iterable
    {
        if (empty($keys)){
            return [];
        }

        $this->validateKeys($keys);

        $values = $this->redis->mget($keys);

        if (!is_array($values) || count($values) !== count($keys)) {
            return [];
        }

        $items = array_combine($keys, $values);

        foreach ($items as $key => $value) {
            if ($value){
                //serialize
                continue;
            }else{
                //Или $missing[] = $key//Потом foreach missing create cacheItem isHit=false
                unset($items[$key]);
            }
        }

        foreach ($keys as $key) {
            if (isset($items[$key])) {
                ///Тут может быть лишний ключ в items???
                $items[$key] = $this->createCacheItem($key, $items[$key], true);
            }else{
                $items[$key] = $this->createCacheItem($key, null, false);
            }
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return $this->redis->exists($key);
    }

    public function clear(): bool
    {
        $this->deferredItems = [];

        return $this->redis->flushdb();
    }

    public function deleteItem(string $key): bool
    {
        //Это тоже можно ставить в очередь deferredItems - чтобы удалять пачкой а не по одному - коммит делать в любом изменяющем состояние методе
        return (bool)$this->redis->del($key);
    }

    public function deleteItems(array $keys): bool
    {
        $this->validateKeys($keys);

        return (bool)$this->redis->del($keys);
    }
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            if (!is_string($key) || $key === '') {
                throw new \InvalidArgumentException('key must be a string');
            }
        }
    }

    public function save(CacheItemInterface $item): bool
    {
        $className = get_class($item);
        $item = (array)$item;

        $key = $item["key"];
        $value = $item["\x00$className\x00value"];
        $expiration = $item["\x00$className\x00expiration"];

        return $this->redis->setEx($key, $expiration, $value);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferredItems[] = $item;

        return true;
    }

    public function commit(): bool
    {

    }
}