<?php
namespace GameX\Modules\TestModule;

use \GameX\Core\Module\BaseModule;
use \GameX\Core\Module\ModuleInterface;
use \GameX\Core\Module\ModuleRoute;
use \GameX\Modules\TestModule\Controllers\TestController;

class Module extends BaseModule implements ModuleInterface {

	/**
	 * @return ModuleRoute[]
	 */
	public function getRoutes() {
		return [
			new ModuleRoute(
				'/test', TestController::class, 'index', 'test_index'
			),
		];
	}
}
