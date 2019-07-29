<?php
use \GameX\Core\Migration;
use \GameX\Models\Task;
use \GameX\Constants\CronConstants;

class CronTasks extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		foreach ($this->getList() as $item) {
			Task::create($item);
		}
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		foreach ($this->getList() as $item) {
			Task::where($item)->delete();
		}
	}

	/**
	 * @return array
	 */
	protected function getList() {
		return [
			[
				'key' => CronConstants::TASK_ONLINE_STATUS,
				'data' => [],
				'execute_at' => 0,
				'status' => Task::STATUS_WAITING,
				'retries' => 0,
				'max_retries' => 3,
			],
			[
				'key' => CronConstants::TASK_PUNISHMENTS_STATUS,
				'data' => [],
				'execute_at' => 0,
				'status' => Task::STATUS_WAITING,
				'retries' => 0,
				'max_retries' => 3,
			],
			[
				'key' => CronConstants::TASK_CLEAR_DATA,
				'data' => [],
				'execute_at' => 0,
				'status' => Task::STATUS_WAITING,
				'retries' => 0,
				'max_retries' => 3,
			]
		];
	}
}


