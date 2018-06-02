<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Views\Twig;
use \GameX\Core\Lang\I18n;
use \GameX\Core\Forms\Form;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

abstract class BaseController {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var I18n|null
     */
    protected $translate = null;

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
     * @param string $path
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function pathFor($path, array $data = [], array $queryParams = []) {
        return $this->getContainer('router')->pathFor($path, $data, $queryParams);
    }

    /**
     * @param $path
     * @param array $data
     * @param array $queryParams
     * @param null $status
     * @return ResponseInterface
     */
    protected function redirect($path, array $data = [], array $queryParams = [], $status = null) {
        return $this->getContainer('response')->withRedirect(
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
     * @param string $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function render($template, array $data = []) {
        /** @var Twig $view */
        $view = $this->getContainer('view');
        return $view->render($this->getContainer('response'), $template, $data);
    }


    /**
     * @param string $type
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function addFlashMessage($type, $message) {
        $this->getContainer('flash')->addMessage($type, $message);
    }

    protected function failRedirect(Exception $e, Form $form) {
        if ($e instanceof FormException) {
            $form->setError($e->getField(), $e->getMessage());
        } elseif ($e instanceof ValidationException) {
            $this->addFlashMessage('error', $e->getMessage());
        } else {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
        }

        $form->saveValues();

        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer('log');
        $logger->error((string) $e);

        return $this->redirectTo($form->getAction());
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
