<?php
namespace GameX\Core\CSRF;

use \GameX\Core\Session\Session;
use \GameX\Core\Utils;

class Token {

    const KEY_LENGTH = 32;
    const KEY_SPACE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
    protected $inputName;

    /**
     * @var string
     */
    protected $inputToken;

    /**
     * @var int
     */
    protected $maxTokens;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var string|null
     */
    protected $newName = null;

    /**
     * @var string|null
     */
    protected $newToken = null;

    /**
     * Token constructor.
     * @param Session $session
     * @param string $sessionKey
     * @param string $inputName
     * @param string $inputToken
     * @param int $maxTokens
     */
    public function __construct(
        Session $session,
        $sessionKey = 'csrf',
        $inputName = 'csrf_name',
        $inputToken = 'csrf_token',
        $maxTokens = 10
    ) {
        $this->session = $session;
        $this->sessionKey = (string) $sessionKey;
        $this->inputName = (string) $inputName;
        $this->inputToken = (string) $inputToken;
        $this->maxTokens = (int) $maxTokens;
        $this->tokens = $session->get($sessionKey, []);
    }

    /**
     * @param $name
     * @param $token
     * @return bool
     */
    public function validateToken($name, $token) {
        if (!$name || !$token) {
            return false;
        }

        if (!array_key_exists($name, $this->tokens)) {
            return false;
        }

        return function_exists('hash_equals')
            ? hash_equals($this->tokens[$name], $token)
            : $this->tokens[$name] === $token;
    }

    /**
     * @return string
     */
    public function getNameKey() {
        return $this->inputName;
    }

    /**
     * @return string
     */
    public function getName() {
        if ($this->newName === null) {
            $this->generateToken();
        }

        return $this->newName;
    }

    /**
     * @return string
     */
    public function getTokenKey() {
        return $this->inputToken;
    }

    /**
     * @return string
     */
    public function getToken() {
        if ($this->newToken === null) {
            $this->generateToken();
        }

        return $this->newToken;
    }

    /**
     * @return string|null
     */
    protected function generateToken() {
        $pieces = [];
        $max = strlen(self::KEY_SPACE) - 1;
        for ($i = 0; $i < self::KEY_LENGTH; $i++) {
            $pieces []= self::KEY_SPACE[random_int(0, $max)];
        }
        $this->newName = implode('', $pieces);
        $this->newToken = Utils::generateToken(32);
        if(count($this->tokens) >= $this->maxTokens) {
            $this->tokens = array_slice($this->tokens, 0, $this->maxTokens - 1);
        }
        $this->tokens[$this->newName] = $this->newToken;
        $this->session->set($this->sessionKey, $this->tokens);
    }
}
