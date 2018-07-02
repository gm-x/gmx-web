<?php

namespace GameX\Core\Auth\Http;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Slim\Http\Request;

class Cookie implements CookieInterface {

    /**
     * The cookie options.
     *
     * @var array
     */
    protected $options = [
        'name'      => 'cartalyst_sentinel',
        'domain'    => '',
        'path'      => '/',
        'secure'    => false,
        'http_only' => false,
    ];

    /**
     * @var Request
     */
    protected $request;

    /**
     * Cookie constructor.
     * @param Request $request
     * @param array $options
     */
    public function __construct(Request $request, $options = []) {
        $this->request = $request;
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options['name'] = $options;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value) {
        $this->setCookie($value, $this->minutesToLifetime(2628000));
    }

    /**
     * {@inheritDoc}
     */
    public function get() {
        $cookie = $this->request->getCookieParam($this->options['name']);
        if ($cookie !== null) {
            return json_decode($cookie);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget() {
        $this->setCookie(null, -2628000);
    }

    /**
     * Takes a minutes parameter (relative to now)
     * and converts it to a lifetime (unix timestamp).
     *
     * @param  int  $minutes
     * @return int
     */
    protected function minutesToLifetime($minutes) {
        return time() + ($minutes * 60);
    }

    /**
     * Sets a PHP cookie.
     *
     * @param  mixed  $value
     * @param  int  $lifetime
     * @return void
     */
    protected function setCookie($value, $lifetime) {
        setcookie(
            $this->options['name'],
            json_encode($value),
            $lifetime,
            $this->options['path'],
            $this->options['domain'],
            $this->options['secure'],
            $this->options['http_only']
        );
    }
}
