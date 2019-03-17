<?php

namespace GameX\Core\Auth\Social;

use \Hybridauth\HttpClient\HttpClientInterface;
use \Hybridauth\Storage\StorageInterface;
use \Hybridauth\Logger\LoggerInterface;

class SocialAuth
{
    /**
     * @var array
     */
    protected $providers;
    
    /**
     * @var ConfigProvider
     */
    protected $config;
    
    /**
     * Storage.
     *
     * @var StorageInterface
     */
    protected $storage;
    
    /**
     * HttpClient.
     *
     * @var HttpClientInterface
     */
    protected $httpClient;
    
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @param array               $providers
     * @param ConfigProvider      $config
     * @param HttpClientInterface $httpClient
     * @param StorageInterface    $storage
     * @param LoggerInterface     $logger
     */
    public function __construct(
        array $providers,
        ConfigProvider $config,
        HttpClientInterface $httpClient = null,
        StorageInterface $storage = null,
        LoggerInterface $logger = null
        
    ) {
        $this->providers = $providers;
        $this->config = $config;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }
    
    public function hasProvider($provider)
    {
        return array_key_exists($provider, $this->providers);
    }
    
    /**
     * @param string $provider
     * @return \Hybridauth\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     */
    public function getProvider($provider)
    {
        if (!$this->hasProvider($provider)) {
            throw new \InvalidArgumentException('Unknown Provider.');
        }
    
        $config = $this->config->getConfig($provider);
        return new $this->providers[$provider]($config, $this->httpClient, $this->storage, $this->logger);
    }
}
