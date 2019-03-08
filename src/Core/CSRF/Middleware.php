<?php
namespace GameX\Core\CSRF;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;

class Middleware {
    
    const METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];
    
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Middleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!in_array($request->getMethod(), self::METHODS)) {
            return $next($request, $response);
        }
        
        if ($this->checkSkipValidate($request)) {
            return $next($request, $response);
        }
        
        $csrf = $this->getCSRF();
        $body = $request->getParsedBody();
        $inputName = $csrf->getNameKey();
        $inputToken = $csrf->getTokenKey();
        $name = (is_array($body) && array_key_exists($inputName, $body))
            ? $body[$inputName]
            : null;
        $token = (is_array($body) && array_key_exists($inputToken, $body))
            ? $body[$inputToken]
            : null;


        if (!$csrf->validateToken($name, $token)) {
            throw new NotAllowedException();
        }
        
        $csrf->purgeToken($name);
    
        return $next($request, $response);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function checkSkipValidate(ServerRequestInterface $request)
    {
        /** @var \Slim\Route $route */
        $route = $request->getAttribute('route');
        if ($route === null) {
            return false;
        }
        $argument = $route->getArgument('csrf_skip');
        if ($argument === null) {
            return false;
        }
        
        return (bool) $argument;
    }
    
    /**
     * @return Token
     */
    protected function getCSRF()
    {
        return $this->container->get('csrf');
    }
}
