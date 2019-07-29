<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Server;
use \xPaw\SourceQuery\SourceQuery;

class RconExecController extends BaseCronController
{
	const ENGINES = [
		'cstrike' => SourceQuery::GOLDSOURCE,
		'csgo' => SourceQuery::SOURCE
	];

    public function run(Task $task)
    {
	    $server = Server::find($task->data['server_id']);
	    $this->getContainer('utils_rcon_exec')->sendCommand($server, $task->data['command']);
	    return new JobResult(true);
    }
}
