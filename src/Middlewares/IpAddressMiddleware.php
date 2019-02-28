<?php

namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Helpers\IpHelper;

class IpAddressMiddleware
{
    protected $attributeName;
    
    public function __construct($attributeName = 'ip_address')
    {
        $this->attributeName = $attributeName;
    }
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if (!$next) {
            return $response;
        }
        $request = $request->withAttribute($this->attributeName, IpHelper::getIPAddress($request));
        return $response = $next($request, $response);
    }
}
