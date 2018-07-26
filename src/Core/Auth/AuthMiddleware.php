<?php
namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;

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
     * @throws NotAllowedException
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
	    /** @var \Slim\Route $route */
	    $route = $request->getAttribute('route');
	    if ($route === null) {
			return $next($request, $response);
		}
        $permission = $route->getArgument('permission');
		$isAuthorized = $route->getArgument('is_authorized');
		$user = $this->auth->getUser();
        if ($permission === null) {
        	if ($isAuthorized === true && !$user) {
				throw new NotAllowedException();
			}
            return $next($request, $response);
        }

        if (!$user || !$user->hasAccess($permission)) {
            throw new NotAllowedException();
        }
		return $next($request, $response);
	}
}
