<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Log\Logger;
use \GameX\Core\Exceptions\ApiException;
use \Exception;

class ApiRequestMiddleware
{
    
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            return $next($request, $response);
        } catch (ApiException $e) {
            return $response->withStatus(500)// TODO: set status code
                ->withJson([
                    'success' => false,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ]);
        } catch (Exception $e) {
            $this->getLogger()->exception($e);
            return $response->withStatus(500)->withJson([
                    'success' => false,
                    'error' => [
                        'code' => \GameX\Core\Exceptions\ApiException::ERROR_SERVER,
                        'message' => 'Something was wrong. Please try again later',
                    ],
                ]);
        }
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->container->get('log');
    }
}
