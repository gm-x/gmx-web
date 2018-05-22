<?php
namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class AuthMiddleware {

	/**
	 * @var Sentinel
	 */
	public $auth;

	/**
	 * AuthMiddleware constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		$this->auth = $container->get('auth');
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return mixed
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
	    /** @var \Slim\Route $route */
	    $route = $request->getAttribute('route');
	    if ($route === null) {
			return $next($request, $response);
		}
        $permission = $route->getArgument('permission');
        if ($permission === null) {
            return $next($request, $response);
        }
        $user = $this->auth->getUser();
        if (!$user || !$user->hasAccess($permission)) {
            $response->withStatus(403)->getBody()->write('Access denied');
            return $response;
        }
		return $next($request, $response);
	}
}
