<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\UsersController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(UsersController::class, 'index'))
        ->setName('admin_users_list')
        ->setArgument('permission', 'admin.users');

    $this
        ->map(['GET', 'POST'], '/{user}/edit', BaseController::action(UsersController::class, 'edit'))
        ->setName('admin_users_edit')
        ->setArgument('permission', 'admin.user.role');
};
