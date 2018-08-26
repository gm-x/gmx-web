<?php
namespace GameX\Core\Auth\Middlewares;

use \Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use \GameX\Core\Lang\Language;
use \GameX\Core\FlashMessages;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Exceptions\NotAllowedException;

abstract class BaseMiddleware {
    
    /** @var  ContainerInterface */
    protected static $container;
    
    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) {
        self::$container = $container;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     * @throws NotAllowedException
     */
    public function __invoke(Request $request, Response $response, callable $next) {
        /** @var UserModel|null $user */
        $user = $request->getAttribute('user');
        if (!$user) {
            /** @var Language $lang */
            $lang = self::$container->get('lang');
            /** @var FlashMessages $flashMessage */
            $flashMessage = self::$container->get('flash');
            $flashMessage->addMessage('error', $lang->format('labels', 'login_redirect'));
            /** @var Router $router */
            $router = self::$container->get('router');
            return $response->withRedirect($router->pathFor('login'));
        }
        
        if (!$this->checkAccess($request, $user)) {
            throw new NotAllowedException();
        }
    
        return $next($request, $response);
    }
    
    /**
     * @param Request $request
     * @param UserModel $user
     * @return bool
     */
    abstract protected function checkAccess(Request $request, UserModel $user);
}
