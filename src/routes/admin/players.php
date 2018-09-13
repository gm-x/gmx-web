<?php
use \GameX\Core\BaseController;
use \GameX\Core\Constants\Routes\Admin\Players;
use \GameX\Core\Constants\Routes\Admin\Privileges;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName(Players::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_LIST));

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName(Players::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName(Players::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_EDIT));

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName(Players::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_DELETE));

	// TODO: Check permissions
    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName(Privileges::ROUTE_LIST)
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'privilege', Permissions::ACCESS_CREATE));

        $this
            ->map(['GET', 'POST'], '/create/{server}', BaseController::action(PrivilegesController::class, 'create'))
            ->setName(Privileges::ROUTE_CREATE);

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName(Privileges::ROUTE_EDIT);

        $this
            ->post('/{privilege}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName(Privileges::ROUTE_DELETE);
    });
};
