<?php

namespace GameX\Core\Auth\Social;

use \Slim\Interfaces\RouterInterface;

class CallbackHelper
{
    
    /**
     * @var string
     */
    protected $basePath;
    
    /**
     * @var RouterInterface
     */
    protected $router;
    
    /**
     * @var string
     */
    protected $path;
    
    /**
     * @param string $basePath
     * @param RouterInterface $router
     * @param string $path
     */
    public function __construct($basePath, RouterInterface $router, $path)
    {
        $this->basePath = $basePath;
        $this->router = $router;
        $this->path = $path;
    }
    
    /**
     * @param string $provider
     * @return string
     */
    public function getCallback($provider)
    {
        return $this->basePath . $this->router->pathFor($this->path, ['provider' => $provider]);
    }
}
