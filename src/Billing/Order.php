<?php

namespace GameX\Billing;

use Slim\App;

class Order
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }


}