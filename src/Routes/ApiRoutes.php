<?php

namespace GameX\Routes;

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
    	$app->group('/server', [$this, 'server']);
    	$app->group('/player', [$this, 'player']);
    	$app->group('/punish', [$this, 'punish']);
    }

    public function server(App $app)
    {
	    $app->post('/privileges', [ServerController::class, 'privileges']);
	    $app->post('/reasons', [ServerController::class, 'reasons']);
	    $app->post('/info', [ServerController::class, 'info']);
	    $app->post('/ping', [ServerController::class, 'ping']);
    }

    public function player(App $app)
    {
	    $app->post('/connect', [PlayerController::class, 'connect']);
	    $app->post('/disconnect', [PlayerController::class, 'disconnect']);
	    $app->post('/assign', [PlayerController::class, 'assign']);
    }

    public function punish(App $app)
    {
	    $app->post('', [PunishController::class, 'index']);
	    $app->post('/immediately', [PunishController::class, 'immediately']);
    }
}
