<?php
use \GameX\Controllers\AccountsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\AccountsConstants;

/** @var \Slim\App $this */

/** @var Permissions $permissions */
$permissions = $this->getContainer()->get('permissions');

$this
    ->get('/accounts', [AccountsController::class, 'index'])
    ->setName(AccountsConstants::ROUTE_LIST)
	->add($permissions->isAuthorizedMiddleware());

$this
    ->get('/accounts/{player}', [AccountsController::class, 'view'])
    ->setName(AccountsConstants::ROUTE_VIEW)
    ->add($permissions->isAuthorizedMiddleware());

$this
    ->post('/accounts/{player}/edit', [AccountsController::class, 'edit'])
    ->setName(AccountsConstants::ROUTE_EDIT)
    ->add($permissions->isAuthorizedMiddleware());
