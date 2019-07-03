<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Server;
use xPaw\SourceQuery\SourceQuery;

class RconExecController extends BaseCronController
{
	const ENGINES = [
		'cstrike' => SourceQuery::GOLDSOURCE,
		'csgo' => SourceQuery::SOURCE
	];

    public function run(Task $task)
    {
	    $server = Server::find($task->data['server_id']);

	    if (!empty($server->rcon) && array_key_exists($server->type, self::ENGINES, true)) {
		    $query = new SourceQuery();
		    $query->Connect($server->ip, $server->port, 10, self::ENGINES[$server->type]);
		    $query->SetRconPassword($server->rcon);
		    $query->Rcon($task->data['command']);
		    $query->Disconnect();
	    }

	    return new JobResult(true);
    }
}
