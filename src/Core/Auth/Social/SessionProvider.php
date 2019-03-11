<?php

namespace GameX\Core\Auth\Social;

use \Hybridauth\Storage\StorageInterface;
use \GameX\Core\Session\Session;

class SessionProvider implements StorageInterface
{
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var string
     */
    protected $namespace;
    
    /**
     * @param Session $session
     * @param string $namespace
     */
    public function __construct(Session $session, $namespace = 'HYBRIDAUTH::STORAGE')
    {
        $this->session = $session;
        $this->namespace = $namespace;
    }
    
    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $data = $this->getData();
        return array_key_exists($key, $data) ? $data[$key] : null;
    }
    
    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $data = $this->getData();
        $data[$key] = $value;
        $this->setData($data);
    }
    
    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $data = $this->getData();
        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            $this->setData($data);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function deleteMatch($key)
    {
        $data = $this->getData();
        foreach ($data as $k => $v) {
            if (strstr($k, $key)) {
                unset($data[$k]);
            }
        }
        $this->setData($data);
    }
    
    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->setData([]);
    }
    
    /**
     * @return array
     */
    protected function getData()
    {
        return $this->session->exists($this->namespace)
            ? (array) $this->session->get($this->namespace)
            : [];
    }
    
    /**
     * @param array $data
     */
    protected function setData(array $data)
    {
        $this->session->set($this->namespace, $data);
    }
}
