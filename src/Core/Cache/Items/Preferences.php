<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Models\Preference;

class Preferences extends CacheItem {

    /**
     * @inheritdoc
     */
    protected function getData() {
        $data = [];
        foreach (Preference::all() as $preference) {
            $data[$preference->getAttribute('key')] = $preference->getAttribute('value');
        }
        return $data;
    }
}