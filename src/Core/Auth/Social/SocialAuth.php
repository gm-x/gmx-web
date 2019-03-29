<?php

namespace GameX\Core\Auth\Social;

use \Hybridauth\Adapter\AdapterInterface;
use \Hybridauth\HttpClient\HttpClientInterface;
use \Hybridauth\Storage\StorageInterface;
use \Hybridauth\Logger\LoggerInterface;
use \Hybridauth\HttpClient\Util;

class SocialAuth
{
    /**
     * @var Provider[]
     */
    protected $providers = [];
    
    /**
     * @var CallbackHelper
     */
    protected $callback;
    
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
     * @var string|null
     */
    protected $redirectUrl = null;
    
    /**
     * @param CallbackHelper      $callback
     * @param HttpClientInterface $httpClient
     * @param StorageInterface    $storage
     * @param LoggerInterface     $logger
     */
    public function __construct(
	    CallbackHelper $callback = null,
        HttpClientInterface $httpClient = null,
        StorageInterface $storage = null,
        LoggerInterface $logger = null
        
    ) {
        $this->callback = $callback;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->httpClient = $httpClient;

        Util::setRedirectHandler([$this, 'redirect']);
    }

	/**
	 * @param string $key
	 * @param Provider $provider
	 * @return $this
	 */
    public function addProvider($key, Provider $provider)
    {
    	$this->providers[$key] = $provider;
    	return $this;
    }

	/**
	 * @param string $provider
	 * @return bool
	 */
    public function hasProvider($provider)
    {
        return array_key_exists($provider, $this->providers);
    }

	/**
	 * @param string $provider
	 * @return AdapterInterface
	 */
    public function getProvider($provider)
    {
        if (!$this->hasProvider($provider)) {
            throw new \InvalidArgumentException('Unknown Provider.');
        }

        $config = $this->providers[$provider]->getConfig();
        $config['callback'] = $this->callback->getCallback($provider);
        $className = $this->providers[$provider]->getClassName();
        return new $className($config, $this->httpClient, $this->storage, $this->logger);
    }

	/**
	 * @return array
	 */
    public function getProviders()
    {
		$providers = [];
		foreach ($this->providers as $key => $provider) {
			$providers[$key] = $provider->getIcon();
		}
		return $providers;
    }

    /**
     * @param string $url
     */
    public function redirect($url)
    {
        $this->redirectUrl = $url;
    }

    /**
     * @return bool
     */
    public function isRedirected()
    {
        return $this->redirectUrl !== null;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
