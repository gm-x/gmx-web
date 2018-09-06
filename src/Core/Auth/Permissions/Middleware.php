<?php
namespace GameX\Core\Auth\Permissions;

use \Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Lang\Language;
use \GameX\Core\FlashMessages;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Permissions\Handlers\HasAccessToGroup;
use \GameX\Core\Exceptions\NotAllowedException;

// TODO; Refactoring manager without recursive links

class Middleware {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function hasAccessToGroup($group) {
        return $this->getMiddleware(new HasAccessToGroup($group));
    }

    protected function getMiddleware(HandleInterface $handler) {
        return function (Request $request, Response $response, callable $next) use ($handler) {
            /** @var UserModel|null $user */
            $user = $request->getAttribute('user');
            if (!$user) {
                return $this->redirectToLogin($response);
            }

            if ($this->checkIsRoot($user)) {
                return $next($request, $response);
            }

            if (!$user->role) {
                throw new NotAllowedException();
            }

            list (, , $args) = $request->getAttribute('routeInfo');
            if (!$handler->checkAccess($this->manager, $user->role, $args)) {
                throw new NotAllowedException();
            }

            return $next($request, $response);
        };
    }

    /**
     * @param Response $response
     * @return Response
     */
    protected function redirectToLogin(Response $response) {
        /** @var Language $lang */
        $lang = $this->container->get('lang');
        /** @var FlashMessages $flashMessage */
        $flashMessage = $this->container->get('flash');
        $flashMessage->addMessage('error', $lang->format('labels', 'login_redirect'));
        /** @var Router $router */
        $router = $this->container->get('router');
        return $response->withRedirect($router->pathFor('login'));
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    protected function checkIsRoot(UserModel $user) {
        /** @var Node $config */
        $config = $this->container->get('config')->getNode('permissions');
        return ((int) $user->id === $config->get('root_user'));
    }
}