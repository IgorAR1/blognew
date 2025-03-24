<?php

namespace App\Core\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ArrayCache implements CacheItemPoolInterface
{

    private function createCacheItem(string $key, ?string $value, bool $isHit): CacheItemInterface
    {
        return new CacheItem($key, $value, $isHit);
    }

    /**
     * @var array<CacheItemInterface>
     */
    private array $items = [];
    private array $defferedItems = [];

    public function getItem(string $key): CacheItemInterface
    {
        if ($this->hasItem($key)) {
            if ()
            $value = $this->items[$key]['value'];
            $isHit = true;

        } else {
            $value = null;
            $isHit = false;
        }

        return $this->createCacheItem($key, $value, $isHit);
    }

    public function getItems(array $keys = []): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            if ($item = $this->getItem($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    public function hasItem(string $key): bool
    {
//        if (isset($this->items[$key]) && $this->items[$key]['expiration'] > new DateTime()) {
//
//            return true;
//        }

        if (isset($this->items[$key])) {
            return true;
        }

        return isset($this->items[$key]) && !$this->deleteItem($key);
    }

    public function clear(): bool
    {
        $this->items = [];

        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->items[$key]);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $item = $item->toArray();
        $this->items[$item['key']]['value'] = $item['value'];
        $this->items[$item['key']]['expiration'] = $item['expiration'];
        $this->items[$item['key']]['time'] = $item['time'];

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->defferedItems[] = $item;

        return true;
    }

    public function commit(): bool
    {
        $this->items = array_merge($this->items, $this->defferedItems);

        return true;
    }
}