<?php

namespace GameX\Billing;

use Slim\App;

interface PayInterface
{
    public function __construct(App $app);
    public function setSum($sum);
    public function setOrder($order);
    public function pay();
    public function getResult();
    public function getErrors();
}