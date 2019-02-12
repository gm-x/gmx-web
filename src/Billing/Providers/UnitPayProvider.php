<?php

namespace GameX\Billing\Providers;

use GameX\Billing\Order;
use GameX\Billing\PayInterface;
use Slim\App;

class UnitPayProvider implements PayInterface
{
    private $app;
    private $order;
    const PAY_URL = '';

    public function __construct(Order $order, App $app)
    {
        $this->app = $app;
        $this->order = $order;
    }

    public function getOrder()
    {
        // TODO: Implement getOrder() method.
    }

    public function pay()
    {
        // TODO: Implement pay() method.
    }

    public function getResult()
    {
        // TODO: Implement getResult() method.
    }

    public function getErrors()
    {
        // TODO: Implement getErrors() method.
    }
}