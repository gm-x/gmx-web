<?php
namespace GameX\Core\Menu;

use \Twig_Extension;
use \Twig_SimpleFunction;

class ViewExtension extends Twig_Extension {

	public function __construct() {
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction(
				'is_current_route',
				[$this, 'isCurrentRoute']
			),
		];
	}

	public function isCurrentRoute(MenuItem $item) {
		return true;
	}
}
