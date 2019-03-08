<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Configuration\Config;

class SecurityMiddleware
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
        $config = $this->getConfig()->getNode('security');
        $header = $config->getNode('content')->get('report')
            ? 'Content-SecurityMiddleware-Policy-Report-Only'
            : 'Content-SecurityMiddleware-Policy';
        $response = $response->withHeader($header, $config->getNode('content')->get('policy'));
        $response = $response->withHeader('Referrer-Policy', $config->get('referer'));
        $response = $response->withHeader('X-XSS-Protection', $config->get('xss'));
        $response = $response->withHeader('X-Frame-Options', $config->get('frame'));
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
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
