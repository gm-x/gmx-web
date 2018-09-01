<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\API\ServerController;
use \GameX\Controllers\API\PlayersController;
use \GameX\Controllers\API\PunishController;

$this->post('/server/info', BaseController::action(ServerController::class, 'info'));
$this->post('/server/map', BaseController::action(ServerController::class, 'map'));
$this->post('/player/connect', BaseController::action(PlayersController::class, 'connect'));
$this->post('/player/disconnect', BaseController::action(PlayersController::class, 'disconnect'));
$this->post('/punish', BaseController::action(PunishController::class, 'index'));
$this->post('/punish/immediately', BaseController::action(PunishController::class, 'immediately'));
