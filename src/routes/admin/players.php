<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName('admin_players_list')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_LIST));

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName('admin_players_create')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName('admin_players_edit')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_EDIT));

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName('admin_players_delete')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_DELETE));

	// TODO: Check permissions
    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName('admin_players_privileges_list')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'privilege', Permissions::ACCESS_CREATE));

        $this
            ->map(['GET', 'POST'], '/create/{server}', BaseController::action(PrivilegesController::class, 'create'))
            ->setName('admin_players_privileges_create');

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName('admin_players_privileges_edit');

        $this
            ->post('/{privilege}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName('admin_players_privileges_delete');
    });
};
