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
	 * @var array
	 */
    protected $titles = [];

	/**
	 * @var array
	 */
    protected $icons = [];
    
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
	 * @param string $title
	 * @param string|null $icon
	 * @param Provider $provider
	 * @return $this
	 */
    public function addProvider($key, $title, $icon, Provider $provider)
    {
    	$this->providers[$key] = $provider;
    	$this->titles[$key] = $title;
    	$this->icons[$key] = $icon;
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
	 * @param string $key
	 * @return string|null
	 */
    public function getTitle($key)
    {
    	return $this->titles[$key] ?: null;
    }

	/**
	 * @param string $key
	 * @return string|null
	 */
    public function getIcon($key)
    {
	    return $this->icons[$key] ?: null;
    }

	/**
	 * @return array
	 */
    public function getProviders()
    {
		return array_keys($this->providers);
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
