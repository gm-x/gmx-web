<?php

namespace GameX\Core\Auth\Social;

use \Slim\Interfaces\RouterInterface;
use \GameX\Core\Configuration\Node;

class ConfigProvider
{
    /**
     * @var Node
     */
    protected $config;
    
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
     * @param Node $config
     * @param string $basePath
     * @param RouterInterface $router
     * @param string $path
     */
    public function __construct(Node $config, $basePath, RouterInterface $router, $path)
    {
        $this->config = $config;
        $this->basePath = $basePath;
        $this->router = $router;
        $this->path = $path;
    }
    
    /**
     * @param $provider
     * @return array
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     */
    public function getConfig($provider)
    {
        $config = $this->config->getNode('social')->get($provider);
        $config['callback'] = $this->getCallback($provider);
        return $config;
    }
    
    /**
     * @param string $provider
     * @return string
     */
    protected function getCallback($provider)
    {
        return $this->basePath . $this->router->pathFor($this->path, ['provider' => $provider]);
    }
}
