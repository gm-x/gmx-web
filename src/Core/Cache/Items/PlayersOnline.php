<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Models\Server;
use \GameX\Models\PlayerSession;

class PlayersOnline extends CacheItem {

    /**
     * @param Server|null $element
     * @return array|mixed
     */
    protected function getData($element) {
        $sessions = [];
        /** @var PlayerSession $session */
        foreach ($element->getActiveSessions() as $session) {
            $player = $session->player;
            $sessions[] = $player->toArray();
        }
        
        return $sessions;
    }

    /**
     * @param $key
     * @param Server|null $element
     * @return mixed|string
     */
    public function getKey($key, $element = null)
    {
        return $element !== null ? $key . '_' . (string)$element->id : $key;
    }

	/**
	 * @inheritdoc
	 */
    protected function getTTL()
    {
        return 60;
    }
}
