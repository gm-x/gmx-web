<?php

namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Models\Server;
use \GameX\Core\Exceptions\InvalidTokenException;

class ApiTokenMiddleware
{
	/**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
	 * @throws InvalidTokenException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $token = $request->getHeaderLine('X-Token');
        if (empty($token)) {
            throw new InvalidTokenException('Token required');
        }

        /** @var \GameX\Models\Server $server */
        $server = Server::where('token', $token)->first();
        if (!$server || !$server->active) {
            throw new InvalidTokenException('Invalid token');
        }
        return $next($request->withAttribute('server', $server), $response);
    }
}
