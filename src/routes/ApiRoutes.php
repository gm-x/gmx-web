<?php

namespace GameX\routes;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Controllers\API\ServerController;
use \GameX\Controllers\API\PlayerController;
use \GameX\Controllers\API\PunishController;

class ApiRoutes extends BaseRoute
{
    /**
     * @param App $app
     */
    public function __invoke(App $app)
    {
        $app->post('/server/privileges', [ServerController::class, 'privileges']);
        $app->post('/server/reasons', [ServerController::class, 'reasons']);
        $app->post('/server/info', [ServerController::class, 'info']);
        $app->post('/server/ping', [ServerController::class, 'ping']);
        $app->post('/player/connect', [PlayerController::class, 'connect']);
        $app->post('/player/disconnect', [PlayerController::class, 'disconnect']);
        $app->post('/player/assign', [PlayerController::class, 'assign']);
        $app->post('/punish', [PunishController::class, 'index']);
        $app->post('/punish/immediately', [PunishController::class, 'immediately']);
    }
}