<?php

namespace GameX\Billing;

use Slim\App;

class Order
{
    /**
     * @var App
     */
    private $app;
    /**
     * @var string
     */
    private $pay_provider;

    /**
     * Order constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getPayProvider()
    {
        return $this->pay_provider;
    }

    /**
     * @param string $pay_provider
     * @return Order
     */
    public function setPayProvider($pay_provider)
    {
        $this->pay_provider = $pay_provider;
        return $this;
    }
}