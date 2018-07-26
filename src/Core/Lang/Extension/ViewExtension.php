<?php
namespace GameX\Core\Lang\Extension;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Twig_Environment;
use \Twig_Extension_InitRuntimeInterface;
use \GameX\Core\Lang\Language;

class ViewExtension extends Twig_Extension implements Twig_Extension_InitRuntimeInterface {

	/**
	 * @var Language
	 */
	protected $language;

	/**
	 * ViewExtension constructor.
	 * @param Language $language
	 */
	public function __construct(Language $language) {
		$this->language = $language;
	}

    /**
     * @param Twig_Environment $environment
     */
    public function initRuntime(Twig_Environment $environment) {
        $language = $this->language->getUserLanguage();
        $languages = $this->language->getLanguages();
        $environment->addGlobal('userLang', $language);
        $environment->addGlobal('userLangName', $languages[$language]);
        $environment->addGlobal('siteLanguages', $this->language->getLanguages());
    }

    /**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction(
				'trans',
				[$this, 'translate'],
				['is_safe' => ['html']]
			),
		];
	}

	/**
	 * @param string $section
	 * @param string $key
	 * @param array $args
	 * @return string
	 */
	public function translate($section, $key, ...$args) {
		return $this->language->format($section, $key, $args);
	}
}
