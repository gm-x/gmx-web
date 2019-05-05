<?php

namespace GameX\Middlewares;


use \Pimple\ServiceProviderInterface;
use \Pimple\Container as PimpleContainer;
use \Psr\Container\ContainerInterface;

use \GameX\Core\CSRF\Middleware as CSRFMiddleware;

class DependencyProvider implements ServiceProviderInterface
{
	/**
	 * @inheritdoc
	 */
	public function register(PimpleContainer $container)
	{
		$container['auth_middleware'] = function (ContainerInterface $container) {
			return new AuthMiddleware($container);
		};

		$container['csrf_middleware'] = function (ContainerInterface $container) {
			return new CSRFMiddleware($container);
		};

		$container['security_middleware'] = function (ContainerInterface $container) {
			return new SecurityMiddleware($container);
		};

		$container['api_token_middleware'] = function (ContainerInterface $container) {
			return new ApiTokenMiddleware($container);
		};

		$container['api_request_middleware'] = function (ContainerInterface $container) {
			return new ApiRequestMiddleware($container);
		};

		$container['ip_address_middleware'] = function () {
			return new IpAddressMiddleware();
		};

		$container['queries_log_middleware'] = function (ContainerInterface $container) {
			return new QueriesLogMiddleware($container);
		};

		$container['redirect_middleware'] = function (ContainerInterface $container) {
			return new RedirectMiddleware($container);
		};

		$container['trail_slash_middleware'] = function () {
			return new TrailSlashMiddleware();
		};
	}
}