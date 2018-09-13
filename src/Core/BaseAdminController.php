<?php
namespace GameX\Core;

use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Constants\Routes\Admin\Players as PlayersRoutes;
use \GameX\Core\Constants\Routes\Admin\Servers as ServersRoutes;

abstract class BaseAdminController extends BaseMainController {

	protected function initMenu() {
		/** @var \GameX\Core\Lang\Language $lang */
		$lang = $this->getContainer('lang');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem(
			    $lang->format('admin_menu','preferences'),
                'admin_preferences_index',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','users'),
                'admin_users_list',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','roles'),
                'admin_roles_list',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','servers'),
                ServersRoutes::ROUTE_LIST,
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','players'),
                PlayersRoutes::ROUTE_LIST,
                []
            ));

        /** @var Twig $view */
        $view = $this->getContainer('view');
		$view->getEnvironment()->addGlobal('menu', $menu);
	}
}
