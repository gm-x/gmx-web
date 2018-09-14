<?php
namespace GameX\Core;

use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\UsersConstants;
use \GameX\Constants\Admin\RolesConstants;

abstract class BaseAdminController extends BaseMainController {

	protected function initMenu() {
		/** @var \GameX\Core\Lang\Language $lang */
		$lang = $this->getContainer('lang');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem(
			    $lang->format('admin_menu','preferences'),
                'admin_preferences_index', []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','users'),
                UsersConstants::ROUTE_LIST, [],
                [ServersConstants::PERMISSION_GROUP, ServersConstants::PERMISSION_KEY]
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','roles'),
                RolesConstants::ROUTE_LIST, [],
                [RolesConstants::PERMISSION_GROUP, RolesConstants::PERMISSION_KEY]
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','servers'),
                ServersConstants::ROUTE_LIST, [],
                [ServersConstants::PERMISSION_GROUP, ServersConstants::PERMISSION_KEY]
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','players'),
                PlayersConstants::ROUTE_LIST, [],
                [PlayersConstants::PERMISSION_GROUP, PlayersConstants::PERMISSION_KEY]
            ));

        /** @var Twig $view */
        $view = $this->getContainer('view');
		$view->getEnvironment()->addGlobal('menu', $menu);
	}
}
