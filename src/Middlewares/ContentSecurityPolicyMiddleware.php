<?php

namespace GameX\Middlewares;

use GameX\Core\Configuration\Node;
use Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class ContentSecurityPolicyMiddleware
{
    /**
     * @var Node
     */
    private $app;

    public function __construct(ContainerInterface $container)
    {
        $this->app = $container->get('config')->getNode('app');
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if ($this->app->get('env', 'production') === 'production') {
            $response = $response->withHeader('Content-Security-Policy', "default-src 'self'");
        }
        return $next($request, $response);
    }
}
