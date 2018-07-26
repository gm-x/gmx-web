<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \GameX\Core\Lang\I18n;
use \GameX\Models\Task;
use \GameX\Core\Jobs\JobResult;

abstract class BaseCronController extends BaseController {

    /**
     * @var string[]
     */
    private static $keys = [];

    /**
     * @param Task $task
     * @return JobResult
     */
    abstract public function run(Task $task);

    /**
     * @param string $key
     * @param string $controller
     */
    public static function registerKey($key, $controller) {
        self::$keys[$key] = $controller;
    }

    /**
     * @param string $key
     * @param Task $task
     * @param ContainerInterface $container
	 * @return JobResult
     */
    public static function execute($key, Task $task, ContainerInterface $container) {
        if (!array_key_exists($key, self::$keys)) {
            return;
        }
        $controller = self::$keys[$key];

        if (!class_exists($controller)) {
            return;
        }
        $controller = new $controller($container);
        return $controller->run($task);
    }
}
