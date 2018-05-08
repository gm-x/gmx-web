<?php
namespace GameX\Core\Auth;

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
	 * @param Sentinel $auth
	 */
	public function __construct(Sentinel $auth) {
		$this->auth = $auth;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return mixed
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
		$user = $this->auth->getUser();
		$request = $request->withAttribute('user', $user);
		return $next($request, $response);
	}
}
