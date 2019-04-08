<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Exceptions\RedirectException;

class RedirectMiddleware
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		try {
			return $next($request, $response);
		} catch (RedirectException $e) {
			return $response->withRedirect($e->getUrl(), $e->getStatus());
		}
	}
}