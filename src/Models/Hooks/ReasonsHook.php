<?php

namespace GameX\Models\Hooks;

use \GameX\Models\Reason;
use \GameX\Models\ServerCommand;

class ReasonsHook
{
    /**
     * @param Reason $reason
     */
    public function saved(Reason $reason)
    {
        ServerCommand::createCommand($reason->server, 'reasons_reload');
    }

    /**
     * @param Reason $reason
     */
    public function deleted(Reason $reason)
    {
        ServerCommand::createCommand($reason->server, 'reasons_reload');
    }
}