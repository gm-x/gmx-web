<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Core\Log\Logger;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Node;

abstract class BaseController {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Config|null
     */
    protected $config = null;

    /**
     * @var Language|null
     */
    protected $translate = null;


    /**
     * @var Logger|null
     */
    protected $logger = null;

	/**
	 * BaseController constructor.
	 * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->init();
    }

    /**
     * Init method
     */
    protected function init() {}

    /**
     * @param $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getContainer($container) {
        return $this->container->get($container);
    }

	/**
	 * @param string $key
	 * @return Node
	 */
    public function getConfig($key) {
        if ($this->config === null) {
            $this->config = $this->getContainer('config');
        }

        return $this->config->getNode($key);
	}

	/**
	 * @param $section
	 * @param $key
	 * @param array $args
	 * @return string
	 */
	public function getTranslate($section, $key, ...$args) {
        if ($this->translate === null) {
            $this->translate = $this->getContainer('lang');
        }
        return $this->translate->format($section, $key, $args);
    }
    
    /**
     * @return Logger
     */
    public function getLogger() {
        if ($this->logger === null) {
            $this->logger = $this->getContainer('log');
        }
    
        return $this->logger;
    }

	/**
	 * @param string $path
	 * @param array $data
	 * @param array $queryParams
	 * @param bool $external
	 * @return string
	 */
	public function pathFor($path, array $data = [], array $queryParams = [], $external = false) {
		$link = $this->getContainer('router')->pathFor($path, $data, $queryParams);
		if (!$external) {
			return $link;
		}

		return (string)$this->getContainer('request')
			->getUri()
			->withPath($link);
	}

	/**
	 * @param $path
	 * @param array $data
	 * @param array $queryParams
	 * @param null $status
	 * @return ResponseInterface
	 */
	protected function redirect($path, array $data = [], array $queryParams = [], $status = null) {
		return $this->redirectTo(
			$this->pathFor($path, $data, $queryParams),
			$status
		);
	}

	/**
	 * @param string $path
	 * @param null $status
	 * @return ResponseInterface
	 */
	protected function redirectTo($path, $status = null) {
		return $this->getContainer('response')->withRedirect($path, $status);
	}

	/**
	 * @param string $controller
	 * @param string $action
	 * @return string
	 */
	public static function action($controller, $action) {
		return $controller . ':' . $action . 'Action';
	}
}
