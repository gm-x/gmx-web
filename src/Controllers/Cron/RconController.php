<?php

namespace GameX\Controllers\Cron;


use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Server;
use \GameX\Core\Rcon\Rcon;

class RconController extends BaseCronController
{
    public function run(Task $task)
    {
    	// TODO: Only for goldsrc
	    $server = Server::find($task->data['server_id']);
	    $rcon = new Rcon($server->ip, $server->port, $server->rcon, ['timeout' => 10]);
	    $rcon->execute($task->data['command']);

        return new JobResult(true);
    }
}
