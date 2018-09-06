<?php
require __DIR__ . '/vendor/autoload.php';
$container = new \Slim\Container([
    'root' => __DIR__ . DIRECTORY_SEPARATOR
]);

$container->register(new \GameX\Core\DependencyProvider());

\GameX\Core\BaseModel::setContainer($container);
\GameX\Core\BaseForm::setContainer($container);
\GameX\Core\Auth\Middlewares\BaseMiddleware::setContainer($container);
\GameX\Core\Utils::setContainer($container);
date_default_timezone_set('UTC');

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;

/** @var \GameX\Core\Log\Logger $logger */
$logger = $container->get('log');

set_error_handler(function ($errno, $error, $file, $line) use ($logger) {
    $logger->error("#$errno: $error in $file:$line");
}, E_ALL);

BaseCronController::registerKey('sendmail', \GameX\Controllers\Cron\SendMailController::class);
BaseCronController::registerKey('punishments', \GameX\Controllers\Cron\PunishmentsController::class);

$task = null;
try {
//    return (php_sapi_name() === 'cli');
    $task = JobHelper::getTask();
    if ($task) {
        $logger->debug('Start cron task', [
            'task' => $task->id
        ]);
        JobHelper::markTask($task, Task::STATUS_IN_PROGRESS);
        $result = BaseCronController::execute($task->key, $task, $container);
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
    }
} catch (Exception $e) {
    $logger->exception($e);
    if ($task) {
        JobHelper::failTask($task);
    }
}
