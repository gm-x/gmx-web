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
use \GameX\Constants\Admin\PreferencesMainConstants;
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
        parent::initMenu();
        /** @var \GameX\Core\Lang\Language $lang */
        $lang = $this->getContainer('lang');
        
        $menu = new Menu($this->container);
        
        $menu
            ->setActiveRoute($this->getActiveMenu())
//            ->add(new MenuItem($lang->format('admin_menu', 'preferences_main'),
//                PreferencesMainConstants::ROUTE_INDEX, [], null, 'fa-database'))
//            ->add(new MenuItem($lang->format('admin_menu', 'preferences'),
//                PreferencesConstants::ROUTE_CACHE, [], null, 'fa-cog'))
            ->add(new MenuItem($lang->format('admin_menu', 'users'),
                UsersConstants::ROUTE_LIST, [], [
                    ServersConstants::PERMISSION_GROUP,
                    ServersConstants::PERMISSION_KEY
                ], 'fa-users'))
            ->add(new MenuItem($lang->format('admin_menu', 'roles'), RolesConstants::ROUTE_LIST, [], [
                    RolesConstants::PERMISSION_GROUP,
                    RolesConstants::PERMISSION_KEY
                ], 'fa-user-lock'))
            ->add(new MenuItem($lang->format('admin_menu', 'servers'), ServersConstants::ROUTE_LIST, [], [
                    ServersConstants::PERMISSION_GROUP,
                    ServersConstants::PERMISSION_KEY
                ], 'fa-server'))
            ->add(new MenuItem($lang->format('admin_menu', 'players'), PlayersConstants::ROUTE_LIST, [],
                [PlayersConstants::PERMISSION_GROUP, PlayersConstants::PERMISSION_KEY], 'fa-user-circle'));

        $itemGroup = new MenuGroup('Preferences', 'fa-database');
        $itemGroup
            ->add(new MenuItem($lang->format('admin_menu', 'preferences_main'),
                PreferencesMainConstants::ROUTE_INDEX, [], null, 'fa-database'))
            ->add(new MenuItem($lang->format('admin_menu', 'preferences'),
                PreferencesConstants::ROUTE_CACHE, [], null, 'fa-cog'));

        $menu->add($itemGroup);
        
        /** @var Twig $view */
        $view = $this->getContainer('view');
        $view->getEnvironment()->addGlobal('adminmenu', $menu);
    }
}
