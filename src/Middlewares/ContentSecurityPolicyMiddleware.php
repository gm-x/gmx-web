<?php

namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class ContentSecurityPolicyMiddleware
{
   
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $response = $response->withHeader('Content-Security-Policy', "default-src 'self'");
        return $next($request, $response);
    }
}
