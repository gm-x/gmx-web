<?php
namespace GameX\Core\Lang\Providers;

use \GameX\Core\Lang\Interfaces\Provider;
use \Slim\Http\Request;
use \GameX\Core\Session\Session;

class SlimProvider implements Provider {
    const SESSION_KEY = 'lang';

    protected $request;
    protected $session;

    /**
     * SlimProvider constructor.
     * @param Request $request
     * @param Session $session
     */
    public function __construct(Request $request, Session $session) {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptLanguageHeader() {
        return $this->request->getServerParam('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionLang() {
        return $this->session->get(self::SESSION_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionLang($lang) {
        $this->session->set(self::SESSION_KEY, $lang);
    }
}
