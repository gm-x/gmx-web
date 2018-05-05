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
     * @param $provider_id
     * @return bool
     */
    public function setProvider($provider_id)
    {
        $provider_class = 'GameX\\Billing\\Providers\\' . ucfirst((string) $provider_id) . 'Provider';
        if (class_exists($provider_class) && ($provider = new $provider_class($this->app) instanceof PayInterface)) {
            $this->provider = $provider;
            return true;
        }
        return false;
    }

    public function pay($order)
    {
        try {
            $this->provider->pay($order);
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }
    }

    public function checkResult($order)
    {

    }
}