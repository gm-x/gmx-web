<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\API\ServerController;
use \GameX\Controllers\API\PlayerController;
use \GameX\Controllers\API\PunishController;

$this->group('/server', function() {
    $this->post('/privileges', BaseController::action(ServerController::class, 'privileges'));
    $this->post('/reasons', BaseController::action(ServerController::class, 'reasons'));
    $this->post('/info', BaseController::action(ServerController::class, 'info'));
    $this->post('/ping', BaseController::action(ServerController::class, 'ping'));
});

$this->group('/player', function() {
    $this->post('/connect', BaseController::action(PlayerController::class, 'connect'));
    $this->post('/disconnect', BaseController::action(PlayerController::class, 'disconnect'));
    $this->post('/assign', BaseController::action(PlayerController::class, 'assign'));
});

$this->group('/punish', function() {
    $this->post('', BaseController::action(PunishController::class, 'index'));
    $this->post('/immediately', BaseController::action(PunishController::class, 'immediately'));
});
