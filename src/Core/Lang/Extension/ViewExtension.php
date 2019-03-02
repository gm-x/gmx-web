<?php
namespace GameX\Core\Lang\Extension;


use \Psr\Container\ContainerInterface;
use \Twig\Extension\AbstractExtension;
use \Twig_SimpleFunction;
use \Twig\Extension\GlobalsInterface;
use \GameX\Core\Lang\Language;

class ViewExtension extends AbstractExtension implements GlobalsInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * ViewExtension constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

    public function getGlobals() {
        return [
            'userLang' => $this->getLanguage()->getUserLanguage(),
            'userLangName' => $this->getLanguage()->getUserLanguageName(),
            'siteLanguages' => $this->getLanguage()->getLanguages(),
        ];
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
		return $this->getLanguage()->format($section, $key, $args);
	}

    /**
     * @return Language
     */
	protected function getLanguage()
    {
	    return $this->container->get('lang');
    }
}
