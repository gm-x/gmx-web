<?php
namespace GameX\Core\CSRF;

use \Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
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
    
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     * @throws NotAllowedException
     */
    public function __invoke(Request $request, Response $response, callable $next)
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
     * @param Request $request
     * @return bool
     */
    protected function checkSkipValidate(Request $request)
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
