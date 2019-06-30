<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \Carbon\Carbon;
use \GameX\Models\Punishment;

class PunishmentsStatus extends BaseCronController
{
    public function run(Task $task)
    {
	    Punishment::where('status', '!=', Punishment::STATUS_PUNISHED)
		    ->where('expired_at', '<', Carbon::now()->toDateTimeString())
		    ->update(['status' => Punishment::STATUS_EXPIRED]);

	    return new JobResult(true, Carbon::now()->addDay());
    }
}
