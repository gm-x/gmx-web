<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Models\Server;
use \GameX\Core\Exceptions\ApiException;
use \GameX\Core\Exceptions\InvalidTokenException;

class ApiTokenMiddleware
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $token = $request->getHeaderLine('X-Token');
            if (empty($token)) {
	            throw new InvalidTokenException('Token required');
            }

            /** @var \GameX\Models\Server $server */
            $server = Server::where('token', $token)->first();
            if (!$server || !$server->active) {
                throw new InvalidTokenException('Invalid token ' . $token);
            }
            return $next($request->withAttribute('server', $server), $response);
        } catch (InvalidTokenException $e) {
        	/** @var \GameX\Core\Log\Logger $log */
        	$log = $this->container->get('log');
        	$log->exception($e);
            return $response->withStatus(403)->withJson([
                    'success' => false,
                    'error' => [
                        'code' => ApiException::ERROR_INVALID_TOKEN,
                        'message' => $e->getMessage(),
                    ],
                ]);
        }
    }
}
