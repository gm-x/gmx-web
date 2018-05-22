<?php
namespace GameX\Core\Lang;

use \GameX\Core\Session\Session;
use \o80\i18n\I18N as I18NBase;
use \o80\i18n\Provider;

class I18n extends I18NBase {

	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @var string
	 */
	protected $defaultLang;

	/**
	 * I18n constructor.
	 * @param Session $session
	 * @param Provider|null $dictProvider
	 * @param string $defaultLang
	 */
	public function __construct(Session $session, Provider $dictProvider = null, $defaultLang = 'en') {
		$this->session = $session;
		parent::__construct($dictProvider);
		$this->useLangFromGET(false);
		$this->setDefaultLang($defaultLang);
	}

	/**
	 * @return array
	 */
	public function getUserLangs() {
		$langs = array();

		$sessionLang = $this->session->get('lang');
		if ($sessionLang !== null) {
			$langs[] = $sessionLang;
		}
		$langs = array_merge($langs, $this->getHttpAcceptLanguages());
		if (!empty($this->defaultLang)) {
			$langs[] = $this->defaultLang;
		}

		return $langs;
	}
}
