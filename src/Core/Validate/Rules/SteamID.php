<?php
namespace GameX\Core\Validate\Rules;

class SteamID extends Regexp {
    /**
     */
    public function __construct() {
        parent::__construct('/^(?:STEAM|VALVE)_\d{1,2}:\d{1,2}:\d+$/');
    }

    /**
     * @return array
     */
    public function getMessage() {
        return ['steamid'];
    }
}
