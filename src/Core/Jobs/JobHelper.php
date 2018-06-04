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
        $task->key = $key;
        $task->data = json_encode($data);
        $task->execute_at = $execute_at !== null ? $execute_at->getTimestamp() : 0;
        $task->status = Task::STATUS_WAITING;
        $task->retries = 0;
        $task->max_retries = 3;
        $task->save();
        return $task;
    }

    /**
     * @return Task|null
     */
    public static function getTask() {
        return Task::where('status', '=', Task::STATUS_WAITING)
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
