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
     * @var array[]
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
	 * @param string $title
	 * @param string|null $icon
	 * @param string $className
	 * @param array $config
	 * @return $this
	 */
    public function addProvider($key, $title, $icon, $className, array $config)
    {
    	$this->providers[$key] = [
    		'title' => $title,
    		'icon' => $icon,
    		'class' => $className,
    		'config' => $config,
	    ];
    	return $this;
    }

	/**
	 * @param string $key
	 * @return bool
	 */
    public function hasProvider($key)
    {
        return array_key_exists($key, $this->providers);
    }

	/**
	 * @param string $key
	 * @return AdapterInterface
	 */
    public function getProvider($key)
    {
        if (!$this->hasProvider($key)) {
            throw new \InvalidArgumentException('Unknown Provider.');
        }

        $config = $this->providers[$key]['config'];
        $config['callback'] = $this->callback->getCallback($key);
        $className = $this->providers[$key]['class'];
        return new $className($config, $this->httpClient, $this->storage, $this->logger);
    }

	/**
	 * @param string $key
	 * @return string|null
	 */
    public function getTitle($key)
    {
    	return $this->hasProvider($key) ? $this->providers[$key]['title'] : null;
    }

	/**
	 * @param string $key
	 * @return string|null
	 */
    public function getIcon($key)
    {
	    return $this->hasProvider($key) ? $this->providers[$key]['icon'] : null;
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
