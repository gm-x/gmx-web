<?php

namespace GameX\Controllers\Cron;


use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Server;

class RconController extends BaseCronController
{
    public function run(Task $task)
    {
        $server = Server::find($task->data['server_id']);
	    $connection = new \GoldSrcServer($server->ip, $server->port);
		$connection->rconAuth($server->rcon);
		$connection->rconExec($task->data['command']);
        
        return new JobResult(true, 10);
    }
}
