<?php

namespace GameX\Core\Auth\Social;

use \Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use \Hybridauth\Hybridauth;
use \Hybridauth\Storage\StorageInterface;
use \Hybridauth\Logger\LoggerInterface;
use \GameX\Core\Session\Session;

class SocialAdapters
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var Hybridauth|null
     */
    protected $hybridauth = null;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @return Hybridauth
     * @throws \Hybridauth\Exception\InvalidArgumentException
     */
    public function getHybridAuth()
    {
        if ($this->hybridauth === null) {
            $this->hybridauth = new Hybridauth([], null, $this->getStorage(), $this->getLogger());
        }
        return $this->hybridauth;
    }
    
    /**
     * @return StorageInterface
     */
    protected function getStorage() {
        /** @var Session $session */
        $session = $this->container->get('session');
        return new SessionProvider($session);
    }
    
    /**
     * @return LoggerInterface
     */
    protected function getLogger() {
        /** @var PsrLoggerInterface $logger */
        $logger = $this->container->get('log');
        return new LoggerProvider($logger);
    }
}
