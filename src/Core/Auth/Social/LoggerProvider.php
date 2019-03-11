<?php

namespace GameX\Core\Auth\Social;

use \Psr\Log\LoggerInterface as PsrLoggerInterface;
use \Hybridauth\Logger\LoggerInterface;

class LoggerProvider implements LoggerInterface
{
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;
    
    /**
     * @param PsrLoggerInterface $logger
     */
    public function __construct(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @inheritdoc
     */
    public function info($message, array $context = array())
    {
        $this->logger->info($message, $context);
    }
    
    /**
     * @inheritdoc
     */
    public function debug($message, array $context = array())
    {
        $this->logger->debug($message, $context);
    }
    
    /**
     * @inheritdoc
     */
    public function error($message, array $context = array())
    {
        $this->logger->error($message, $context);
    }
    
    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}
