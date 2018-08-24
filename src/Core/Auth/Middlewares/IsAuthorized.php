<?php
namespace GameX\Core\Auth\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;

class IsAuthorized {
 
	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
     * @throws NotAllowedException
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $user = $request->getAttribute('user');
        if (!$request->getAttribute('user')) {
            throw new NotAllowedException();
        }
        
        return $next($request, $response);
	}
}
