<?php

namespace GameX\Core\Auth\Http;

use Cartalyst\Sentinel\Cookies\CookieInterface;

class FakeCookie implements CookieInterface {
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
