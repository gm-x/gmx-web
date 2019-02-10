<?php

namespace GameX\Billing;

use Slim\App;

/**
 * Class Payment
 * @package GameX\Billing
 */
class Payment
{
    /**
     * @var App
     */
    private $app;
    /**
     * @var PayInterface
     */
    private $provider;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function setProvider(Order $order)
    {
        $provider_class = 'GameX\\Billing\\Providers\\' . ucfirst((string) $order->getPayProvider()) . 'ProviderInterface';
        if (class_exists($provider_class) && ($provider = new $provider_class($order, $this->app) instanceof PayInterface)) {
            $this->provider = $provider;
            return true;
        }
        return false;
    }

    public function pay()
    {
        try {
            $this->provider->pay();
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }
    }

    public function checkResult()
    {

    }
}