<?php
namespace GameX\Controllers\Cron;


use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Punishment;
use \Illuminate\Database\Capsule\Manager;
use \Carbon\Carbon;

class PunishmentsController extends BaseCronController {
    public function run(Task $task) {
    	/** @var Manager $db */
    	$db = self::getContainer('db');
    	$db
			->getConnection()
			->table('punishments')
			->where('status', '!=', Punishment::STATUS_PUNISHED)
			->where('expired_at', '<', Carbon::now()->toDateTimeString())
			->update(['status' => Punishment::STATUS_EXPIRED]);

		return new JobResult(true, 10);
    }
}
