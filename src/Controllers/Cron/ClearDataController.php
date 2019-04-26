<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;

class ClearDataController extends BaseCronController
{
    public function run(Task $task)
    {
    	// TODO: Clear old player sessions, Tasks, Logs
        return new JobResult(true);
    }
}
