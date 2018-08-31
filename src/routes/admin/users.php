<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\UsersController;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Middlewares\HasAccessToPermission;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(UsersController::class, 'index'))
        ->setName('admin_users_list')
        ->add(new HasAccessToPermission('admin', 'user', Manager::ACCESS_LIST));

    $this
        ->get('/{user}', BaseController::action(UsersController::class, 'view'))
        ->setName('admin_users_view')
        ->add(new HasAccessToPermission('admin', 'user', Manager::ACCESS_VIEW));

    $this
        ->map(['GET', 'POST'], '/{user}/edit', BaseController::action(UsersController::class, 'edit'))
        ->setName('admin_users_edit')
        ->add(new HasAccessToPermission('admin', 'user_role', Manager::ACCESS_VIEW | Manager::ACCESS_EDIT));
};
