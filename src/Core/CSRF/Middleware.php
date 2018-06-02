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
            $inputName = $this->token->getNameKey();
            $inputToken = $this->token->getTokenKey();
            $name = (is_array($body) && array_key_exists($inputName, $body))
                ? $body[$inputName]
                : null;
            $token = (is_array($body) && array_key_exists($inputToken, $body))
                ? $body[$inputToken]
                : null;


            if (!$this->token->validateToken($name, $token)) {
                $failureCallable = $this->failure;
                return $failureCallable($request, $response, $next);
            }
        }

        return $next($request, $response);
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
