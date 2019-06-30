<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \Carbon\Carbon;
use \GameX\Models\PlayerSession;

class OnlineStatus extends BaseCronController
{
    public function run(Task $task)
    {
	    PlayerSession::where('status', PlayerSession::STATUS_ONLINE)
		    ->where('ping_at', '<=', Carbon::now()->subMinutes(1))
		    ->update(['status' => PlayerSession::STATUS_OFFLINE, 'disconnected_at' => Carbon::now()]);

	    return new JobResult(true, Carbon::now()->addMinutes(1));
    }
}
