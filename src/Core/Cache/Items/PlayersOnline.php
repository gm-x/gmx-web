<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Core\Auth\Models\RoleModel;

class PlayersOnline extends CacheItem {

    /**
     * @inheritdoc
     */
    protected function getData() {
//        return Player::where('server_id', $server->id)->count();
    }
}
