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
        ServerCommand::createCommand($group->server, 'group_changed', (string)$group->id);
    }

    /**
     * @param Group $group
     */
    public function deleted(Group $group)
    {
        ServerCommand::createCommand($group->server, 'group_removed', (string)$group->id);
    }
}