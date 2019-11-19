<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Models\UserModel;

class AuthMiddleware
{
    
    /**
     * @var Sentinel
     */
    public $auth;
    
    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
    	#FIXME: Catch NotActivatedException and continue execution without user
	    #NOTE: Notify user about activation
	    #NOTE: Also need catch all other places in source code
        /** @var UserModel $user */
        $user = $this->auth->getUser();
        return $next($request->withAttribute('user', $user), $response);
    }
}
