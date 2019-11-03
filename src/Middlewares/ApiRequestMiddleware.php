<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Log\Logger;
use \GameX\Core\Exceptions\InvalidTokenException;
use \GameX\Core\Exceptions\ValidationException;
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
        } catch (InvalidTokenException $e) {
	        $this->getLogger()->exception($e);
	        $this->getLogger()->debug($request->getHeaderLine('X-Token'));
	        return $response->withStatus(403)->withJson([
		        'success' => false,
		        'error' => [
			        'code' => -2,
			        'message' => $e->getMessage(),
		        ],
	        ]);
        } catch (ValidationException $e) {
	        $this->getLogger()->exception($e);
	        $this->getLogger()->debug($request->getBody()->__toString());
	        return $response->withStatus(400)
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
                        'code' => -1,
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
