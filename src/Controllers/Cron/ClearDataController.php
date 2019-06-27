<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \Carbon\Carbon;
use \GameX\Models\PlayerSession;
use \Illuminate\Database\Eloquent\Builder;

class ClearDataController extends BaseCronController
{
    public function run(Task $task)
    {
    	PlayerSession::where('status', PlayerSession::STATUS_OFFLINE)
		    ->where('created_at', '<', Carbon::now()->subMonth())
		    ->delete();

	    Task::where(function(Builder $query) {
		    $query
			    ->where('status', Task::STATUS_DONE)
			    ->orWhere('status', Task::STATUS_FAILED);
	        })
		    ->where('created_at', '<', Carbon::now()->subMonth())
		    ->get();

	    return new JobResult(true, Carbon::now()->addMonth(1));
    }
}
