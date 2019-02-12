<?php

namespace GameX\Core;

use \GameX\Core\Session\Session;
use \ArrayAccess;

class FlashMessages implements ArrayAccess
{
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var string
     */
    protected $sessionKey;
    
    /**
     * Messages from previous request
     *
     * @var string[]
     */
    protected $fromPrevious = [];
    
    /**
     * @param Session $session
     * @param null|string $sessionKey
     */
    public function __construct(Session $session, $sessionKey = null)
    {
        $this->session = $session;
        $this->sessionKey = $sessionKey !== null ? $sessionKey : 'flash_messages';
        
        $this->fromPrevious = $session->get($this->sessionKey, []);
        $session->delete($this->sessionKey);
    }
    
    /**
     * Add flash message for the next request
     *
     * @param string $key The key to store the message under
     * @param mixed $message Message to show on next request
     * @return $this
     */
    public function addMessage($key, $message)
    {
        $this->session->pushArray($this->sessionKey, $key, $message);
        return $this;
    }
    
    /**
     * Get flash messages
     *
     * @return array Messages to show for current request
     */
    public function getMessages()
    {
        return $this->fromPrevious;
    }
    
    /**
     * Get Flash Message
     *
     * @param string $key The key to get the message from
     * @return array Returns the message
     */
    public function getMessage($key)
    {
        return $this->hasMessage($key) ? $this->fromPrevious[$key] : [];
    }
    
    /**
     * Has Flash Message
     *
     * @param string $key The key to get the message from
     * @return bool Whether the message is set or not
     */
    public function hasMessage($key)
    {
        return array_key_exists($key, $this->fromPrevious);
    }
    
    /**
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->hasMessage($key);
    }
    
    /**
     * @param string $key
     * @return array
     */
    public function offsetGet($key)
    {
        return $this->getMessage($key);
    }
    
    /**
     * @param string $key
     * @param string $value
     */
    public function offsetSet($key, $value)
    {
        $this->addMessage($key, $value);
    }
    
    /**
     * @param string $key
     */
    public function offsetUnset($key)
    {
    }
}
