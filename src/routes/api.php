<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\API\ServerController;
use \GameX\Controllers\API\PlayerController;
use \GameX\Controllers\API\PunishController;

$this->post('/server/info', BaseController::action(ServerController::class, 'info'));
$this->post('/server/map', BaseController::action(ServerController::class, 'map'));
$this->post('/player/connect', BaseController::action(PlayerController::class, 'connect'));
$this->post('/player/disconnect', BaseController::action(PlayerController::class, 'disconnect'));
$this->post('/player/assign', BaseController::action(PlayerController::class, 'assign'));
$this->post('/punish', BaseController::action(PunishController::class, 'index'));
$this->post('/punish/immediately', BaseController::action(PunishController::class, 'immediately'));
