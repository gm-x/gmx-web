<?php
namespace GameX\Core\Lang;

use \Twig_Extension;
use \Twig_SimpleFunction;
//use \o80\i18n\I18N;

class ViewExtention extends Twig_Extension {

	/**
	 * @var I18N
	 */
	protected $i18n;

	/**
	 * ViewExtention constructor.
	 * @param I18N $i18n
	 */
	public function __construct(I18N $i18n) {
		$this->i18n = $i18n;
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
	 * @param array|null $args
	 * @return string
	 */
	public function translate($section, $key, array $args = null) {
		return $args !== null
			? $this->i18n->format($section, $key, $args)
			: $this->i18n->get($section, $key);
	}
}
