<?php
namespace GameX\Core\CSRF;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;

class Middleware {
    
    const METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];

    /**
     * @var Token
     */
    protected $token;
    
    /**
     * Middleware constructor.
     * @param Token $token
     */
    public function __construct(Token $token) {
        $this->token = $token;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        if (!in_array($request->getMethod(), self::METHODS)) {
            return $next($request, $response);
        }
        
        if ($this->checkSkipValidate($request)) {
            return $next($request, $response);
        }
        $body = $request->getParsedBody();
        $inputName = $this->token->getNameKey();
        $inputToken = $this->token->getTokenKey();
        $name = (is_array($body) && array_key_exists($inputName, $body))
            ? $body[$inputName]
            : null;
        $token = (is_array($body) && array_key_exists($inputToken, $body))
            ? $body[$inputToken]
            : null;


        if (!$this->token->validateToken($name, $token)) {
            throw new NotAllowedException();
        }
    
        return $next($request, $response);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function checkSkipValidate(ServerRequestInterface $request) {
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
}
