<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;

abstract class BaseAdminController extends BaseController {

	/**
	 * BaseController constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->initAdminMenu($container->get('view'), $container->get('menu'));
	}

	/**
	 * @return string
	 */
	abstract protected function getActiveAdminMenu();

	private function initAdminMenu(Twig $view, Menu $menu) {
		/** @var \o80\i18n\I18N $lang */
		$lang = $this->getContainer('lang');

		$menu->setActiveRoute($this->getActiveAdminMenu());

		$menu->add(new MenuItem($lang->get('adminMenu', 'users'), 'admin_users_list', [], 'admin.users'));
		$menu->add(new MenuItem($lang->get('adminMenu', 'roles'), 'admin_roles_list', [], 'admin.roles'));
		$menu->add(new MenuItem($lang->get('adminMenu', 'servers'), 'admin_servers_list', [], 'admin.servers'));
		$menu->add(new MenuItem($lang->get('adminMenu', 'players'), 'admin_players_list', [], 'admin.players'));

		$view->getEnvironment()->addGlobal('adminMenu', $menu);
	}
}
