<?php
namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Models\Server;

class ApiTokenMiddleware {
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        try {
            if (!preg_match('/Basic\s+(?P<token>.+?)$/i', $request->getHeaderLine('Authorization'), $matches)) {
                throw new NotAllowedException();
            }
        
            $token = base64_decode($matches['token']);
            if (!$token) {
                throw new NotAllowedException();
            }
        
            list ($token) = explode(':', $token);
            if (empty($token)) {
                throw new NotAllowedException();
            }
        
            $server = Server::where('token', $token)->first();
            if (!$server || $server->ip !== $request->getAttribute('ip_address')) {
                throw new NotAllowedException();
            }
            return $next($request->withAttribute('server_id', $server->id), $response);
        } catch (NotAllowedException $e) {
            return $response
                ->withJson([
                    'success' => false,
                    'error' => [
                        'code' => 403,
                        'message' => 'Bad token',
                    ],
                ]);
        }
    }
}
