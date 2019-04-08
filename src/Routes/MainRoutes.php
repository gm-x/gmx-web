<?php

namespace GameX\Routes;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\UserController;
use \GameX\Controllers\SettingsController;
use \GameX\Controllers\AccountsController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Constants\IndexConstants;
use \GameX\Constants\UserConstants;
use \GameX\Constants\SettingsConstants;
use \GameX\Constants\AccountsConstants;

class MainRoutes extends BaseRoute
{
    public function __invoke(App $app)
    {
        $app
            ->get('/', [IndexController::class, 'index'])
            ->setName(IndexConstants::ROUTE_INDEX);

        $app
            ->post('/lang', [IndexController::class, 'language'])
            ->setName('language')
            ->setArgument('csrf_skip', true);

        $app
            ->get('/punishments', [PunishmentsController::class, 'index'])
            ->setName('punishments');

        $this->user($app);
        $this->settings($app);
        $this->accounts($app);
    }

    public function user(App $app)
    {
        $app
            ->map(['GET', 'POST'], '/register', [UserController::class, 'register'])
            ->setName('register');

        $app
            ->map(['GET', 'POST'], '/activation/{code}', [UserController::class, 'activate'])
            ->setName('activation');

        $app
            ->map(['GET', 'POST'], '/login', [UserController::class, 'login'])
            ->setName('login');

        $app
            ->map(['GET', 'POST'], '/reset_password', [UserController::class, 'resetPassword'])
            ->setName('reset_password');

        $app
            ->map(['GET', 'POST'], '/reset_password/{code}', [UserController::class, 'resetPasswordComplete'])
            ->setName('reset_password_complete');

        $app
            ->get('/logout', [UserController::class, 'logout'])
            ->setName('logout');

        $app
            ->map(['GET', 'POST'], '/auth/{provider}', [UserController::class, 'social'])
            ->setName(UserConstants::ROUTE_SOCIAL);
    }

    public function settings(App $app)
    {
        $app
            ->map(['GET', 'POST'], '/settings', [SettingsController::class, 'index'])
            ->setName(SettingsConstants::ROUTE_INDEX)
            ->add($this->getPermissions()->isAuthorizedMiddleware());

    }

    public function accounts(App $app)
    {
        $app
            ->get('/accounts', [AccountsController::class, 'index'])
            ->setName(AccountsConstants::ROUTE_LIST)
            ->add($this->getPermissions()->isAuthorizedMiddleware());

        $app
            ->get('/accounts/{player:\d+}', [AccountsController::class, 'view'])
            ->setName(AccountsConstants::ROUTE_VIEW)
            ->add($this->getPermissions()->isAuthorizedMiddleware());

        $app
            ->post('/accounts/{player:\d+}/edit', [AccountsController::class, 'edit'])
            ->setName(AccountsConstants::ROUTE_EDIT)
            ->add($this->getPermissions()->isAuthorizedMiddleware());
    }
}