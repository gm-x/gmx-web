<?php
namespace GameX\Modules\TestModule;

use \GameX\Core\Module\BaseModule;
use \GameX\Core\Module\ModuleInterface;
use \GameX\Core\Module\ModuleRoute;
use \GameX\Modules\TestModule\Controllers\TestController;
use \GameX\Core\Menu\MenuItem;

class Module extends BaseModule implements ModuleInterface {

	/**
	 * @return ModuleRoute[]
	 */
	public function getRoutes() {
		return [
			new ModuleRoute(
				['GET'], '/test', TestController::class, 'index', 'test'
			),
		];
	}

	/**
	 * @return \GameX\Core\Menu\MenuItem[]
	 */
	public function getMenuItems() {
		return [
			new MenuItem('Test', 'test', [], null),
		];
	}
}
