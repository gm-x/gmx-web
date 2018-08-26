<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Middlewares\HasAccessToPermission;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName('admin_players_list')
        ->add(new HasAccessToPermission('admin', 'player', Manager::ACCESS_LIST));

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName('admin_players_create')
        ->add(new HasAccessToPermission('admin', 'player', Manager::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName('admin_players_edit')
        ->add(new HasAccessToPermission('admin', 'player', Manager::ACCESS_EDIT));

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName('admin_players_delete')
        ->add(new HasAccessToPermission('admin', 'player', Manager::ACCESS_DELETE));

	// TODO: Check permissions
    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName('admin_players_privileges_list');

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(PrivilegesController::class, 'create'))
            ->setName('admin_players_privileges_create');

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName('admin_players_privileges_edit');

        $this
            ->post('/{privilege}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName('admin_players_privileges_delete');

        $this
            ->get('/groups', BaseController::action(PrivilegesController::class, 'groups'))
            ->setName('admin_players_privileges_groups');
    });
};
