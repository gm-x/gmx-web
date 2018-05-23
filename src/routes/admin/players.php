<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PlayersController;

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
};
