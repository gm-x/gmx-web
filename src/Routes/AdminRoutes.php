<?php

namespace GameX\Routes;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Constants\Admin\AdminConstants;
use \GameX\Controllers\Admin\AdminController;
use \GameX\Routes\Admin\PreferencesRoutes;
use \GameX\Routes\Admin\UsersRoutes;
use \GameX\Routes\Admin\RolesRoutes;
use \GameX\Routes\Admin\PlayersRoutes;
use \GameX\Routes\Admin\ServersRoutes;

class AdminRoutes extends BaseRoute
{
    public function __invoke(App $app)
    {
        $app
            ->get('', [AdminController::class, 'index'])
            ->setName(AdminConstants::ROUTE_INDEX)
            ->add($this->getPermissions()->hasAccessToGroupMiddleware('admin'));

        $app
	        ->get('/charts/emulators', [AdminController::class, 'emulators'])
	        ->setName(AdminConstants::ROUTE_EMULATORS)
	        ->add($this->getPermissions()->hasAccessToGroupMiddleware('admin'));
        $app
	        ->get('/charts/online', [AdminController::class, 'online'])
	        ->setName(AdminConstants::ROUTE_ONLINE)
	        ->add($this->getPermissions()->hasAccessToGroupMiddleware('admin'));

	    $app->group('/preferences', PreferencesRoutes::class);
	    $app->group('/users', UsersRoutes::class);
	    $app->group('/roles', RolesRoutes::class);
	    $app->group('/players', PlayersRoutes::class);
	    $app->group('/servers', ServersRoutes::class);
    }
}
