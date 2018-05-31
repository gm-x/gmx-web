<?php
namespace GameX\Core;

use \Twig_Extension;
use \Twig_SimpleFunction;

class Twig_Dump extends Twig_Extension {
	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction(
				'dump',
				[$this, 'dump'],
				['is_safe' => ['html']]
			),
		];
	}

	public function dump($var) {
		ob_start();
		var_dump($var);
		return ob_get_clean();
	}
}
