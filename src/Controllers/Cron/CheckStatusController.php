<?php

namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \Carbon\Carbon;
use \GameX\Models\Punishment;
use \GameX\Models\PlayerSession;
use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Database\Eloquent\Builder;

class CheckStatusController extends BaseCronController
{
    public function run(Task $task)
    {
	    /** @var Manager $db */
	    $db = self::getContainer('db');

	    $db->getConnection()->table('punishments')->where('status', '!=',
		    Punishment::STATUS_PUNISHED)->where('expired_at', '<',
		    Carbon::now()->toDateTimeString())->update(['status' => Punishment::STATUS_EXPIRED]);

	    PlayerSession::where('status', PlayerSession::STATUS_OFFLINE)
		    ->where('created_at', '<', Carbon::now()->subMonth())
		    ->update();

	    return new JobResult(true, Carbon::now()->addMonth(1));
    }
}
