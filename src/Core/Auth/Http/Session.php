<?php

namespace GameX\Core\Auth\Http;

use Cartalyst\Sentinel\Sessions\SessionInterface;
use \GameX\Core\Session\Session as SessionHelper;

class Session implements SessionInterface {

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * @var SessionHelper
     */
    protected $session;

    public function __construct(SessionHelper $session, $key = null) {
        $this->session = $session;
        if ($key !== null) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value) {
        $this->session->set($this->key, json_encode($value));
    }

    /**
     * {@inheritDoc}
     */
    public function get() {
        $session = $this->session->get($this->key);
        return $session ? json_decode($session, true) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function forget() {
        $this->session->delete($this->key);
    }
}
