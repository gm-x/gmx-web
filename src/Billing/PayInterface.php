<?php

namespace GameX\Billing;

interface PayInterface
{
    public function __construct(Order $order);
    public function getOrder();
    public function pay();
    public function getResult();
    public function getErrors();
}