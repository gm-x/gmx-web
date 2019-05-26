<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Configuration\Config;
use \Monolog\Logger;
use \Illuminate\Database\Capsule\Manager;
use \Slim\Http\Request;

class QueriesLogMiddleware
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
		$response = $next($request, $response);

		if (!$this->getConfig()->getNode('log')->get('queries', false)) {
			return $response;
		}


		$logger = $this->getLogger();

		$request = $this->getRequest();
		$logger->debug(sprintf(
			'Request: %s %s', $request->getMethod(), $request->getUri()->getPath()
		));

		$queries = $this->getDatabase()->getConnection()->getQueryLog();
		foreach ($queries as $query) {
			$log = sprintf('Query (time %0.2f): %s', $query['time'], $query['query']);
			$logger->debug($log, $query['bindings']);
		}

		return $response;
	}

	/**
	 * @return Config
	 */
	protected function getConfig()
	{
		return $this->container->get('config');
	}

	/**
	 * @return Logger
	 */
	protected function getLogger()
	{
		return $this->container->get('log');
	}

	/**
	 * @return Manager
	 */
	protected function getDatabase()
	{
		return $this->container->get('db');
	}

	/**
	 * @return Request
	 */
	protected function getRequest()
	{
		return $this->container->get('request');
	}
}