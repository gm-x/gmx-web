<?php
namespace GameX\Core\Lang;

use \Twig_Extension;
use \Twig_SimpleFunction;

class ViewExtension extends Twig_Extension {

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
