<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName('admin_players_list')
        ->setArgument('permission', 'admin.players');

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName('admin_players_create')
		->setArgument('permission', 'admin.players');

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName('admin_players_edit')
        ->setArgument('permission', 'admin.players');

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName('admin_players_delete')
		->setArgument('permission', 'admin.players');

    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName('admin_players_privileges_list')
            ->setArgument('permission', 'admin.players.privileges');

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(PrivilegesController::class, 'create'))
            ->setName('admin_players_privileges_create')
            ->setArgument('permission', 'admin.players.privileges');

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName('admin_players_privileges_edit')
            ->setArgument('permission', 'admin.players.privileges');

        $this
            ->post('/{privileged}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName('admin_players_privileges_delete')
            ->setArgument('permission', 'admin.players.privileges');
    });
};
