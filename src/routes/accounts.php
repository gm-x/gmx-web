<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\AccountsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\AccountsConstants;

/** @var \Slim\App $this */

/** @var Permissions $permissions */
$permissions = $this->getContainer()->get('permissions');

$this
    ->get('/accounts', BaseController::action(AccountsController::class, 'index'))
    ->setName(AccountsConstants::ROUTE_LIST)
	->add($permissions->isAuthorizedMiddleware());

$this
    ->get('/accounts/{player}', BaseController::action(AccountsController::class, 'view'))
    ->setName(AccountsConstants::ROUTE_VIEW)
    ->add($permissions->isAuthorizedMiddleware());
