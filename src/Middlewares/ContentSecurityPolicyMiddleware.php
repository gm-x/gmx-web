<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Configuration\Config;

class ContentSecurityPolicyMiddleware
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
        $config = $this->getConfig()->getNode('security')->getNode('content');
        $header = $config->get('report') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        $response = $response->withHeader($header, $config->get('policy'));
        return $next($request, $response);
    }
    
    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->container->get('config');
    }
}
