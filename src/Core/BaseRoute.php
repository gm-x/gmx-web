<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\App;
use \GameX\Core\Auth\Permissions;

abstract class BaseRoute
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param App $app
     */
    abstract function __invoke(App $app);

    /**
     * @return Permissions
     */
    protected function getPermissions()
    {
        return $this->container->get('permissions');
    }
}