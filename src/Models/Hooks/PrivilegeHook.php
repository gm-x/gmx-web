<?php

namespace GameX\Models\Hooks;

use \GameX\Models\Privilege;
use \GameX\Models\ServerCommand;

class PrivilegeHook
{
    /**
     * @param Privilege $privilege
     */
    public function saved(Privilege $privilege)
    {
        ServerCommand::createCommand(
            $privilege->group->server,
            'privilege_changed',
            (string)$privilege->player_id
        );
    }

    /**
     * @param Privilege $privilege
     */
    public function deleted(Privilege $privilege)
    {
        ServerCommand::createCommand(
            $privilege->group->server,
            'privilege_removed',
            (string)$privilege->player_id
        );
    }
}