<?php

namespace GameX\Core\Auth;

use Cartalyst\Sentinel\Sessions\SessionInterface;
use SlimSession\Helper;

class Session implements SessionInterface {

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * @var Helper
     */
    protected $session;

    public function __construct(Helper $session, $key = null) {
        $this->session = $session;
        if ($key !== null) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value) {
        $this->session->set($this->key, serialize($value));
    }

    /**
     * {@inheritDoc}
     */
    public function get() {
        $session = $this->session->get($this->key);
        if ($session) {
            return unserialize($session);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget() {
        $this->session->delete($this->key);
    }
}
