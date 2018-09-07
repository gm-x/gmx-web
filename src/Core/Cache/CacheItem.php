<?php
namespace GameX\Core\Cache;

use \Stash\Interfaces\ItemInterface;

abstract class CacheItem {

    /**
     * @param ItemInterface $item
     * @return mixed
     */
    public function get(ItemInterface $item) {
        $data = $item->get();
        if ($item->isMiss()) {
            $item->lock();
            $data = $this->getData();
            $item->set($data);
            $item->save();
        }
        return $data;
    }

    public function clear(ItemInterface $item) {
        return $item->clear();
    }

    /**
     * @return mixed
     */
    abstract protected function getData();
}