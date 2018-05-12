<?php
namespace GameX\Core\Session;

interface SessionSettingsInterface {

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getLifeTime();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string|null
     */
    public function getDomain();

    /**
     * @return bool
     */
    public function getSecure();

    /**
     * @return bool
     */
    public function getHttpOnly();

    /**
     * @return bool
     */
    public function getAutoRefresh();

    /**
     * @return \SessionHandlerInterface|null
     */
    public function getHandler();

    /**
     * @return array
     */
    public function getIniSettings();
}
