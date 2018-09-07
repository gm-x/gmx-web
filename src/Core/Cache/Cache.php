<?php
namespace GameX\Core\Cache;

use \Stash\Interfaces\PoolInterface;
use \Stash\Interfaces\ItemInterface;
use \Stash\Driver\AbstractDriver;
use \Stash\Pool;

class Cache {

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var CacheItem[]
     */
    protected $items = [];

    /**
     * @param AbstractDriver $driver
     */
    public function __construct(AbstractDriver $driver) {
        $this->pool = new Pool($driver);
    }

    /**
     * @param string $key
     * @param CacheItem $item
     */
    public function add($key, CacheItem $item) {
        $this->items[$key] = $item;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NotFoundException
     */
    public function get($key) {
        return $this->getCacheItem($key)->get($this->getItem($key));
    }

    /**
     * @param $key
     * @return bool
     * @throws NotFoundException
     */
    public function clear($key) {
        return $this->getCacheItem($key)->clear($this->getItem($key));
    }

    /**
     * @param string $key
     * @return CacheItem
     * @throws NotFoundException
     */
    protected function getCacheItem($key) {
        if (!array_key_exists($key, $this->items)) {
            throw new NotFoundException('Key ' . $key . ' not found');
        }

        return $this->items[$key];
    }

    /**
     * @param string $key
     * @return ItemInterface
     */
    protected function getItem($key) {
        return $this->pool->getItem($key);
    }
}