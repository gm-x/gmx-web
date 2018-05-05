<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\App;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use \Slim\Exception\NotFoundException;
use Slim\Views\Twig;

abstract class BaseController {
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * @param $action
     * @return \Closure
     */
    public function action($action) {
        $actionName = $action . 'Action';
        $controller = $this;
        $callable = function (Request $request, Response $response, array $args) use ($controller, $actionName) {
            $controller->setRequest($request);
            $controller->setResponse($response);

            if (method_exists($controller, 'init')) {
                $controller->init();
            }

            if (!method_exists($controller, $actionName)) {
                throw new NotFoundException($request, $response);
            }

            return call_user_func([$controller, $actionName], $args);
        };

        return $callable;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response) {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() {
        return $this->app->getContainer();
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
}
