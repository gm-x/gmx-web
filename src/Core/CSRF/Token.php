<?php
namespace GameX\Core\CSRF;

use \GameX\Core\Session\Session;

class Token {

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @var string
     */
    protected $inputKey;

    /**
     * @var string|null
     */
    protected $oldToken = null;

    /**
     * @var string|null
     */
    protected $newToken = null;

    /**
     * CSRFToken constructor.
     * @param Session $session
     * @param string $sessionKey
     * @param string $inputKey
     */
    public function __construct(Session $session, $sessionKey = 'csrf_token', $inputKey = 'csrf') {
        $this->session = $session;
        $this->sessionKey = (string) $sessionKey;
        $this->inputKey = (string) $inputKey;
    }

    /**
     * @return string|null
     */
    public function getToken() {
        if ($this->oldToken === null) {
            $this->oldToken = $this->session->get($this->sessionKey);
            $this->session->delete($this->sessionKey);
        }

        return $this->oldToken;
    }

    /**
     * @return string|null
     */
    public function generateToken() {
        if ($this->newToken === null) {
            $this->newToken = bin2hex(random_bytes(32));
            $this->session->set($this->sessionKey, $this->newToken);
        }

        return $this->newToken;
    }

    /**
     * @return string
     */
    public function getInputKey() {
        return $this->inputKey;
    }
}
