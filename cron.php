<?php
require __DIR__ . '/vendor/autoload.php';
$container = new \Slim\Container([
    'root' => __DIR__ . DIRECTORY_SEPARATOR
]);
$container['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
include __DIR__ . '/src/dependencies.php';

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;

BaseCronController::setContainer($container);

BaseCronController::registerKey('sendmail', \GameX\Controllers\Cron\SendMailController::class);
BaseCronController::registerKey('monitoring', \GameX\Controllers\Cron\MonitoringlController::class);

$task = JobHelper::getTask();
if ($task === null) {
    die();
}

JobHelper::markTask($task, Task::STATUS_IN_PROGRESS);
try {
    $result = BaseCronController::execute($task->key, $task);
    if ($result->getStatus()) {
    	if ($result->getNextTimeExecute() === null) {
			JobHelper::markTask($task, Task::STATUS_DONE);
		} else {
    		$task->execute_at = $result->getNextTimeExecute()->getTimestamp();
    		JobHelper::markTask($task, Task::STATUS_WAITING);
		}
	} else {
		$task->retries++;
		if ($task->retries >= $task->max_retries) {
			JobHelper::markTask($task, Task::STATUS_FAILED);
		} else {
			JobHelper::markTask($task, Task::STATUS_WAITING);
		}
	}
} catch (Exception $e) {
    JobHelper::failTask($task);
}
