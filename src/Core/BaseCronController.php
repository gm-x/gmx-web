<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \GameX\Core\Lang\I18n;
use \GameX\Models\Task;

abstract class BaseCronController {

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var string[]
     */
    private static $keys = [];

    /**
     * @var I18n|null
     */
    protected $translate = null;

	/**
	 * BaseController constructor.
	 */
    public function __construct() {
        $this->init();
    }

    /**
     * Init method
     */
    protected function init() {}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
    public function getConfig($key, $default = null) {
    	$config = $this->getContainer('config');
    	return array_key_exists($key, $config) ? $config[$key] : $default;
	}

	public function getTranslate($section, $key, array $args = null) {
        if ($this->translate === null) {
            $this->translate = $this->getContainer('lang');
        }
        return $args !== null
            ? $this->translate->format($section, $key, $args)
            : $this->translate->get($section, $key);
    }

    /**
     * @param Task $task
     * @return bool
     */
    abstract public function run(Task $task);

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) {
        self::$container = $container;
    }

    /**
     * @param $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getContainer($container) {
        return self::$container->get($container);
    }

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
     */
    public static function execute($key, Task $task) {
        if (!array_key_exists($key, self::$keys)) {
            return;
        }
        $controller = self::$keys[$key];

        if (!class_exists($controller)) {
            return;
        }
        $controller = new $controller();
        $controller->run($task);
    }
}
