<?php
namespace GameX\Core\Module;

interface ModuleInterface {

	/**
	 * @return ModuleRoute[]
	 */
	public function getRoutes();

	/**
	 * @return ModuleRoute[]
	 */
	public function getAdminRoutes();

	/**
	 * @return ModuleRoute[]
	 */
	public function getApiRoutes();

	/**
	 * @return ModuleCron[]
	 */
	public function getCronKeys();

	/**
	 * @return \GameX\Core\Menu\MenuItem[]
	 */
	public function getMenuItems();

	/**
	 * @return \GameX\Core\Menu\MenuItem[]
	 */
	public function getAdminMenuItems();

	/**
	 * @return array
	 */
	public function getDependencies();

	/**
	 * @return array
	 */
	public function getMiddleware();
}
