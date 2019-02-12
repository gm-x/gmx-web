<?php

namespace GameX\Billing;

use Slim\App;

interface PayInterface
{
    public function __construct(Order $order, App $app);
    public function getOrder();
    public function pay();
    public function getResult();
    public function getErrors();
}