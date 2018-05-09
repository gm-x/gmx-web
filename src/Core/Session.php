<?php

namespace GameX\Core;


class Session extends \Slim\Middleware\Session {
    public function __construct($settings = []) {
        parent::__construct($settings);
        $this->startSession();
    }
}
