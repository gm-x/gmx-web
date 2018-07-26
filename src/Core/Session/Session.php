<?php

namespace GameX\Core\Session;

class Session {

    /**
     * @var SessionSettingsInterface
     */
    protected $settings;

    /**
     * @var bool|null
     */
    protected $isActive = null;

    /**
     * Session constructor.
     * @param SessionSettingsInterface|null $settings
     */
    public function __construct(SessionSettingsInterface $settings = null) {
        $this->settings = $settings !== null
            ? $settings
            : new SessionSettings();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value) {
        $this->startSession();
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null) {
        return $this->exists($key)
            ? $_SESSION[$key]
            : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key) {
        $this->startSession();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete($key) {
        if ($this->exists($key)) {
            unset($_SESSION[$key]);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $array
     * @param mixed $value
     * @return $this
     */
    public function pushArray($key, $array, $value) {
        if (!$this->exists($key)) {
            $_SESSION[$key] = [];
        }

        if (!array_key_exists($array, $_SESSION[$key])) {
            $_SESSION[$key][$array] = [];
        }

        $_SESSION[$key][$array][] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param string $array
     * @param array $default
     * @return array
     */
    public function getArray($key, $array, $default = []) {
        return ($this->exists($key) && array_key_exists($array, $_SESSION[$key]))
            ? (array) $_SESSION[$key][$array]
            : $default;
    }

    /**
     * @param string $key
     * @param string $array
     * @param array $value
     * @return $this
     */
    public function setArray($key, $array, array $value) {
        if (!$this->exists($key)) {
            $_SESSION[$key] = [];
        }

        $_SESSION[$key][$array] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param string $array
     * @return $this
     */
    public function clearArray($key, $array) {
        if ($this->exists($key) && array_key_exists($array, $_SESSION[$key])) {
            unset($_SESSION[$key][$array]);
        }

        return $this;
    }

    /**
     * Start session
     */
    public function startSession() {
        if ($this->getIsActive() || headers_sent()) {
            return;
        }

        session_set_cookie_params(
            $this->settings->getLifeTime(),
            $this->settings->getPath(),
            $this->settings->getDomain(),
            $this->settings->getSecure(),
            $this->settings->getHttpOnly()
        );

        $name = $this->settings->getName();
        if ($this->settings->getAutoRefresh() && isset($_COOKIE[$name])) {
            setcookie(
                $name,
                $_COOKIE[$name],
                time() + $this->settings->getLifeTime(),
                $this->settings->getPath(),
                $this->settings->getDomain(),
                $this->settings->getSecure(),
                $this->settings->getHttpOnly()
            );
        }

        session_name($name);

        $handler = $this->settings->getHandler();
        if ($handler !== null) {
            $handler = new $handler();
            session_set_save_handler($handler, true);
        }

        session_cache_limiter(false);

        session_start();

        $this->isActive = true;
    }

    /**
     * @return bool
     */
    protected function getIsActive() {
        if ($this->isActive === null) {
            $this->isActive = session_status() === PHP_SESSION_ACTIVE;
        }

        return $this->isActive;
    }
}
