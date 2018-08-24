<?php
namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Monolog\Logger;
use \GameX\Core\Exceptions\ApiException;
use \Exception;

class ApiRequestMiddleware {
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        try {
            return $next($request, $response);
        } catch (ApiException $e) {
            return $response
                ->withStatus(500) // TODO: set status code
                ->withJson([
                    'success' => false,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ]);
        } catch (Exception $e) {
            $this->logger->error((string)$e);
            return $response
                ->withStatus(500)
                ->withJson([
                    'success' => false,
                    'error' => [
                        'code' => \GameX\Core\Exceptions\ApiException::ERROR_SERVER,
                        'message' => 'Something was wrong. Please try again later',
                    ],
                ]);
        }
    }
}
