<?php

namespace GameX\routes;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Constants\Admin\AdminConstants;
use \GameX\Controllers\Admin\AdminController;

class AdminRoutes extends BaseRoute
{
    public function __invoke(App $app)
    {
        $app
            ->get('', [AdminController::class, 'index'])
            ->setName(AdminConstants::ROUTE_INDEX)
            ->add($this->getPermissions()->hasAccessToGroupMiddleware('admin'));

        $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $app->group('/preferences', include  $root . 'preferences.php');
        $app->group('/users', include  $root . 'users.php');
        $app->group('/roles', include $root . 'roles.php');
        $app->group('/servers', include $root . 'servers.php');
        $app->group('/players', include $root . 'players.php');
    }
}
