<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\API\PrivilegesController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(PrivilegesController::class, 'index'));
};
