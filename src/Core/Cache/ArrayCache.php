<?php

namespace App\Core\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ArrayCache extends AbstractCache
{
    private array $items = [];
    private array $deferredItems = [];//?

    public function getItem(string $key): CacheItemInterface
    {
        if ($isHit = $this->hasItem($key)) {
            $value = $this->items[$key]['value'];
        } else {
            $value = null;
        }

        return $this->createCacheItem($key, $value, $isHit);
    }
///Тут возможно нужен генератор
    public function getItems(array $keys = []): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result = $this->getItem($key);
        }

        return $result;
    }

    public function hasItem(string $key): bool
    {
        if (isset($this->items[$key]) && $this->isNotExpired($key)) {
            return true;
        }

        return !$this->deleteItem($key);
    }

    private function isNotExpired(string $key): bool
    {
        return ($this->items[$key]['expiration'] > microtime(true) || !null === $this->items[$key]['expiration']);
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
        $className = get_class($item);
        $item = (array)$item;

        $key = $item["key"];
        $value = $item["\x00$className\x00value"];
        $expiration = $item["\x00$className\x00expiration"];

        if (isset($this->items[$key]) && !$this->isNotExpired($key)) {
            return $this->deleteItem($key);
        }

        $this->items[$key] = [
            'value' => $value,
            'expiration' => $expiration
        ];

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferredItems[] = $item;

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferredItems as $item) {
            $this->save($item);
        }

        $this->deferredItems = [];

        return true;
    }
}