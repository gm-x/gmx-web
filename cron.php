<?php
require __DIR__ . '/vendor/autoload.php';

use \Slim\Container;
use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Core\Configuration\Providers\PHPProvider;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\DependencyProvider;
use \GameX\Core\BaseModel;
use \GameX\Core\BaseForm;
use \GameX\Constants\CronConstants;
use \GameX\Controllers\Cron\SendMailController;
use \GameX\Controllers\Cron\RconExecController;
use \GameX\Controllers\Cron\ClearDataController;
use \GameX\Controllers\Cron\OnlineStatusController;
use \GameX\Controllers\Cron\PunishmentsStatusController;

$container = new Container([
    'root' => __DIR__ . DIRECTORY_SEPARATOR
]);

try {
    $configProvider = new PHPProvider(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
    $config = new Config($configProvider);
} catch (CantLoadException $e) {
    die('Can\'t load configuration file');
}

$container->register(new DependencyProvider($config));

BaseModel::setContainer($container);
BaseForm::setContainer($container);
date_default_timezone_set('UTC');


/** @var \GameX\Core\Log\Logger $logger */
$logger = $container->get('log');

set_error_handler(function ($errno, $error, $file, $line) use ($logger) {
    $logger->error("#$errno: $error in $file:$line");
}, E_ALL);

BaseCronController::registerKey(CronConstants::TASK_SENDMAIL, SendMailController::class);
BaseCronController::registerKey(CronConstants::TASK_RCON_EXEC, RconExecController::class);
BaseCronController::registerKey(CronConstants::TASK_CLEAR_DATA, ClearDataController::class);
BaseCronController::registerKey(CronConstants::TASK_ONLINE_STATUS, OnlineStatusController::class);
BaseCronController::registerKey(CronConstants::TASK_PUNISHMENTS_STATUS, PunishmentsStatusController::class);

$task = null;
try {
//    return (php_sapi_name() === 'cli');
    $task = JobHelper::getTask();
    if ($task) {
//        $logger->debug('Start cron task', [
//            'task' => $task->id
//        ]);
        JobHelper::markTask($task, Task::STATUS_IN_PROGRESS);
        $result = BaseCronController::execute($task->key, $task, $container);
        if ($result && $result instanceof JobResult &&  $result->getStatus()) {
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
