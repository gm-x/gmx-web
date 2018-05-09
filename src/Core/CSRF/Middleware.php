<?php

namespace GameX\Core\CSRF;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Middleware {

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var callable
     */
    protected $failure;

    public function __construct(Token $token, $failure = null) {
        $this->token = $token;
        $this->failure = $failure !== null
            ? $failure
            : $this->getFailureCallable();
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $body = $request->getParsedBody();
            $inputKey = $this->token->getInputKey();
            $token = (is_array($body) && array_key_exists($inputKey, $body))
                ? $body[$inputKey]
                : null;


            if (!$this->validateToken($token)) {
                $failureCallable = $this->failure;
                return $failureCallable($request, $response, $next);
            }
        }

        return $next($request, $response);
    }

    /**
     * @param $token
     * @return bool
     */
    protected function validateToken($token) {
        if ($token === null) {
            return false;
        }

        $expectedToken = $this->token->getToken();
        if ($expectedToken === null) {
            return false;
        }

        return function_exists('hash_equals')
            ? hash_equals($expectedToken, $token)
            : $expectedToken === $token;
    }

    /**
     * @return callable
     */
    protected function getFailureCallable() {
        return function (ServerRequestInterface $request, ResponseInterface $response, $next) {
            $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
            $body->write('Failed CSRF check!');
            return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
        };
    }
}
