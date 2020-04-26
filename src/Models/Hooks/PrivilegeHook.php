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
        $model = new ServerCommand();
        $model->fill([
            'server_id' => $privilege->group->server_id,
            'command' => 'privilege_changed',
            'data' => (string)$privilege->player_id,
            'status' => ServerCommand::STATUS_ACTIVE,
        ]);
        $model->save();
    }

    /**
     * @param Privilege $privilege
     */
    public function deleted(Privilege $privilege)
    {
        $model = new ServerCommand();
        $model->fill([
            'server_id' => $privilege->group->server_id,
            'command' => 'privilege_removed',
            'data' => (string)$privilege->player_id,
            'status' => ServerCommand::STATUS_ACTIVE,
        ]);
        $model->save();
    }
}