<?php

namespace GameX\Core\Auth\Http;

use Cartalyst\Sentinel\Sessions\SessionInterface;
use \GameX\Core\Session\Session as SessionHelper;

class FakeSession implements SessionInterface {
    /**
     * {@inheritDoc}
     */
    public function put($value) {}

    /**
     * {@inheritDoc}
     */
    public function get() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function forget() {}
}
