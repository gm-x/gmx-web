<?php
namespace GameX\Core\Jobs;

use \DateTime;
use \GameX\Models\Task;

class JobHelper {

    /**
     * @param string $key
     * @param array $data
     * @param DateTime|null $execute_at
     * @return Task
     */
    public static function createTask($key, array $data = [], DateTime $execute_at = null) {
        $task = new Task();
        $task->fill([
	        'key' => $key,
	        'data' => $data,
	        'execute_at' => $execute_at !== null ? $execute_at->getTimestamp() : 0,
	        'status' => Task::STATUS_WAITING,
	        'retries' => 0,
	        'max_retries' => 3,
        ]);
        $task->save();
        return $task;
    }

	/**
	 * @param string $key
	 * @param array $data
	 * @param DateTime|null $execute_at
	 * @param callable|null $filter
	 * @return Task
	 */
	public static function createTaskIfNotExists($key, array $data = [], DateTime $execute_at = null, callable $filter = null) {
		$task = null;
		if ($filter !== null) {
			$task = Task::where([
				'status' => Task::STATUS_WAITING,
				'key' => $key
			])->get()->filter($filter)->first();
		} else {
			$task = Task::where([
				'status' => Task::STATUS_WAITING,
				'key' => $key
			])->first();
		}

		if ($task === null) {
			$task = static::createTask($key, $data, $execute_at);
		}

		return $task;
	}

    /**
     * @return Task|null
     */
    public static function getTask() {
        return Task::where('status', Task::STATUS_WAITING)
            ->where('execute_at', '<', time())
            ->orderBy('execute_at', 'desc')
            ->first();
    }

    public static function markTask(Task $task, $status) {
        $task->status = $status;
        $task->save();
        return $task;
    }

    public static function failTask(Task $task) {
		$task->retries++;
		if ($task->max_retries > 0 && $task->retries >= $task->max_retries) {
			self::markTask($task, Task::STATUS_FAILED);
		} else {
			self::markTask($task, Task::STATUS_WAITING);
		}
    }
}
