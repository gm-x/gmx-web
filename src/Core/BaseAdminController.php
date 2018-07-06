<?php
namespace GameX\Core;

use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;

abstract class BaseAdminController extends BaseMainController {

	protected function initMenu() {
		/** @var Twig $view */
		$view = $this->getContainer('view');

		/** @var \o80\i18n\I18N $lang */
		$lang = $this->getContainer('lang');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem($lang->get('adminMenu', 'preferences'), 'admin_preferences_index', [], 'admin.preferences'))
			->add(new MenuItem($lang->get('adminMenu', 'users'), 'admin_users_list', [], 'admin.users'))
			->add(new MenuItem($lang->get('adminMenu', 'roles'), 'admin_roles_list', [], 'admin.roles'))
			->add(new MenuItem($lang->get('adminMenu', 'servers'), 'admin_servers_list', [], 'admin.servers'))
			->add(new MenuItem($lang->get('adminMenu', 'players'), 'admin_players_list', [], 'admin.players'));

		$modules = $this->getContainer('modules');
		/** @var \GameX\Core\Module\ModuleInterface $module */
		foreach ($modules as $module) {
			$items = $module->getAdminMenuItems();
			foreach ($items as $item) {
				$menu->add($item);
			}
		}

		$view->getEnvironment()->addGlobal('menu', $menu);
	}
}
