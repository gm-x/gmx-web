<?php

namespace GameX\Core\Lang\Providers;

use \GameX\Core\Lang\Interfaces\Provider;
use \Slim\Http\Request;

class SlimProvider implements Provider
{
    const COOKIE_KEY = 'lang';
    const COOKIE_LIFETIME = 31556926; // 1 year

    /**
     * @var Request
     */
    protected $request;

    /**
     * SlimProvider constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptLanguageHeader()
    {
        return $this->request->getServerParam('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * {@inheritDoc}
     */
    public function getLang()
    {
        return array_key_exists(self::COOKIE_KEY, $_COOKIE) ? $_COOKIE[self::COOKIE_KEY] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function setLang($lang)
    {
        setcookie(
            self::COOKIE_KEY,
            $lang,
            time() + self::COOKIE_LIFETIME,
            '/',
            null,
            false,
            false
        );
    }
}
