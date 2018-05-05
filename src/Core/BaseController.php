<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\App;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use Slim\Views\Twig;

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

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    /**
     * @return Request
     */
    public function getRequest() {
        return $this->container->request;
    }
    /**
     * @return Response
     */
    public function getResponse() {
        return $this->container->response;
    }


    /**
     * @param $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getContainer($container = null) {
        $ret = $this->app->getContainer();

        return $container ? $ret->get((string) $container) : $ret;
    }

    /**
     * @param $path
     * @param array $data
     * @param array $queryParams
     * @param null $status
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function redirect($path, array $data = [], array $queryParams = [], $status = null) {
        /** @var Router $router */
        $router = $this->getContainer('router');
        return $this->getResponse()->withRedirect($router->pathFor($path, $data, $queryParams), $status);
    }


    /**
     * @param $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function render($template, array $data = []) {
        /** @var Twig $view */
        $view = $this->getContainer('view');
        return $view->render($this->getResponse(), $template, $data);
    }


    /**
     * @param $type
     * @param $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function addFlashMessage($type, $message) {
        $this->getContainer('flash')->addMessage($type, $message);
    }

    public static function action($controller, $action) {
        return $controller . ':' . $action . 'Action';
    }
}
