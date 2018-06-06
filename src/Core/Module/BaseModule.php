<?php

namespace GameX\Core\Module;

abstract class BaseModule implements ModuleInterface {

	/**
	 * @return ModuleRoute[]
	 */
	public function getRoutes() {
		return [];
	}

	/**
	 * @return ModuleRoute[]
	 */
	public function getAdminRoutes() {
		return [];
	}

	/**
	 * @return ModuleRoute[]
	 */
	public function getApiRoutes() {
		return [];
	}

	/**
	 * @return ModuleCron[]
	 */
	public function getCronKeys() {
		return [];
	}

	/**
	 * @return \GameX\Core\Menu\MenuItem[]
	 */
	public function getMenuItems() {
		return [];
	}

	/**
	 * @return \GameX\Core\Menu\MenuItem[]
	 */
	public function getAdminMenuItems() {
		return [];
	}

	/**
	 * @return array
	 */
	public function getDependencies() {
		return [];
	}

	/**
	 * @return array
	 */
	public function getMiddleware() {
		return [];
	}
}
