<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Menu\MenuGroup;
use \GameX\Constants\Admin\AdminConstants;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\UsersConstants;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PreferencesConstants;

abstract class BaseAdminController extends BaseMainController
{

    /**
     * BaseController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->getBreadcrumbs()->add(
            $this->getTranslate('menu', 'admin'),
            $this->pathFor(AdminConstants::ROUTE_INDEX)
        );
    }
    
    protected function initMenu()
    {
//        parent::initMenu();
        /** @var \GameX\Core\Lang\Language $lang */
        $lang = $this->getContainer('lang');
        
        $menu = new Menu($this->container);
        
        $menu
            ->setActiveRoute($this->getActiveMenu());

        $itemGroup = new MenuGroup($lang->format('admin_menu', 'servers'), 'fa-network-wired');
        $itemGroup
            ->add(new MenuItem($lang->format('admin_menu', 'list'),ServersConstants::ROUTE_LIST, [], [
                ServersConstants::PERMISSION_GROUP,
                ServersConstants::PERMISSION_KEY
            ], 'fa-list'))
            ->add(new MenuItem($lang->format('admin_menu', 'players'), PlayersConstants::ROUTE_LIST, [],
                [PlayersConstants::PERMISSION_GROUP, PlayersConstants::PERMISSION_KEY], 'fa-user-circle'));
        $menu->add($itemGroup);

        $itemGroup = new MenuGroup($lang->format('admin_menu', 'users'), 'fa-users');
        $itemGroup
            ->add(new MenuItem($lang->format('admin_menu', 'list'),
                UsersConstants::ROUTE_LIST, [], [
                    ServersConstants::PERMISSION_GROUP,
                    ServersConstants::PERMISSION_KEY
                ], 'fa-list'))
            ->add(new MenuItem($lang->format('admin_menu', 'roles'), RolesConstants::ROUTE_LIST, [], [
                RolesConstants::PERMISSION_GROUP,
                RolesConstants::PERMISSION_KEY
            ], 'fa-user-lock'));
        $menu->add($itemGroup);

        $itemGroup = new MenuGroup($lang->format('admin_menu', 'preferences'), 'fa-database');
        $itemGroup
            ->add(new MenuItem($lang->format('admin_menu', 'preferences_main'),
                PreferencesConstants::ROUTE_MAIN, [], null, 'fa-globe'))
            ->add(new MenuItem($lang->format('admin_menu', 'preferences_email'),
                PreferencesConstants::ROUTE_EMAIL, [], null, 'fa-envelope'))
            ->add(new MenuItem($lang->format('admin_menu', 'preferences_update'),
                PreferencesConstants::ROUTE_UPDATE, [], null, 'fa-code-branch'))
	        ->add(new MenuItem($lang->format('admin_menu', 'preferences_cache'),
		        PreferencesConstants::ROUTE_CACHE, [], null, 'fa-file'))
	        ->add(new MenuItem($lang->format('admin_menu', 'preferences_cron'),
		        PreferencesConstants::ROUTE_CRON, [], null, 'fa-tasks'))
	        ->add(new MenuItem($lang->format('admin_menu', 'preferences_social'),
		        PreferencesConstants::ROUTE_SOCIAL, [], null, 'fa-share-alt'));
        $menu->add($itemGroup);
        
        /** @var Twig $view */
        $view = $this->getContainer('view');
        $view->getEnvironment()->addGlobal('adminmenu', $menu);
    }
}
