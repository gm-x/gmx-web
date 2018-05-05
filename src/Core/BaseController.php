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
     * @return ContainerInterface
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @param $path
     * @param array $data
     * @param array $queryParams
     * @param null $status
     * @return Response
     */
    protected function redirect($path, array $data = [], array $queryParams = [], $status = null) {
        /** @var Router $router */
        $router = $this->getContainer()->get('router');
        return $this->getResponse()->withRedirect($router->pathFor($path, $data, $queryParams), $status);
    }

    /**
     * @param $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function render($template, array $data = []) {
        /** @var Twig $view */
        $view = $this->getContainer()->get('view');
        return $view->render($this->getResponse(), $template, $data);
    }

    /**
     * @param $type
     * @param $message
     */
    protected function addFlashMessage($type, $message) {
        $this->getContainer()->get('flash')->addMessage($type, $message);
    }

    public static function action($controller, $action) {
        return $controller . ':' . $action . 'Action';
    }
}
