<?php

namespace GameX\Models\Hooks;

use \GameX\Models\Group;
use \GameX\Models\ServerCommand;

class GroupHook
{
    /**
     * @param Group $group
     */
    public function saved(Group $group)
    {
        $model = new ServerCommand();
        $model->fill([
            'server_id' => $group->server_id,
            'command' => 'group_changed',
            'data' => (string)$group->id,
            'status' => ServerCommand::STATUS_ACTIVE,
        ]);
        $model->save();
    }

    /**
     * @param Group $group
     */
    public function deleted(Group $group)
    {
        $model = new ServerCommand();
        $model->fill([
            'server_id' => $group->server_id,
            'command' => 'group_removed',
            'data' => (string)$group->id,
            'status' => ServerCommand::STATUS_ACTIVE,
        ]);
        $model->save();
    }
}