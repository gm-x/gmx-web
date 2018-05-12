<?php
namespace GameX\Core\Session;

use SessionHandlerInterface;

class SessionSettings implements SessionSettingsInterface {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $lifeTime;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string|null
     */
    protected $domain;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * @var bool
     */
    protected $autoRefresh;

    /**
     * @var SessionHandlerInterface|null
     */
    protected $handler;

    /**
     * @var array
     */
    protected $iniSettings;

    public function __construct(array $settings = []) {
        $this->name = array_key_exists('name', $settings)
            ? (string) $settings['name']
            : 'session_id';

        $lifetime = array_key_exists('lifeTime', $settings)
            ? (string) $settings['lifeTime']
            : '20 minutes';
        $this->lifeTime = strtotime($lifetime) - time();

        $this->path = array_key_exists('path', $settings)
            ? (string) $settings['path']
            : '/';

        $this->domain = array_key_exists('domain', $settings)
            ? (string) $settings['domain']
            : null;

        $this->secure = array_key_exists('secure', $settings)
            ? (bool) $settings['secure']
            : false;

        $this->httpOnly = array_key_exists('httpOnly', $settings)
            ? (bool) $settings['httpOnly']
            : true;

        $this->autoRefresh = array_key_exists('autoRefresh', $settings)
            ? (bool) $settings['autoRefresh']
            : false;

        $this->handler =
            (array_key_exists('handler', $settings) && $settings['handler'] instanceof SessionHandlerInterface)
            ? $settings['handler']
            : null;

        $this->iniSettings = array_key_exists('iniSettings', $settings) && is_array($settings['iniSettings'])
            ? $settings['iniSettings']
            : [];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLifeTime() {
        return $this->lifeTime;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function getSecure() {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function getHttpOnly() {
        return $this->httpOnly;
    }

    /**
     * @return bool
     */
    public function getAutoRefresh() {
        return $this->autoRefresh;
    }

    /**
     * @return callable|null
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getIniSettings() {
        return $this->iniSettings;
    }
}
