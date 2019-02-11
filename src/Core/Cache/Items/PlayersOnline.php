<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Models\Server;
use \GameX\Models\PlayerSession;

class PlayersOnline extends CacheItem {

    /**
     * @inheritdoc
     */
    protected function getData() {
        $sessions = [];
        foreach (Server::all() as $server) {
            $sessions[$server->id] = [];
            /** @var PlayerSession $session */
            foreach ( $server->getActiveSessions() as $session) {
                $player = $session->player;
                $sessions[$server->id][] = [
                    'id' => $player->id,
                    'nick' => $player->nick
                ];
            }
        }
        
        return $sessions;
    }
}
