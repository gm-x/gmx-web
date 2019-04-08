<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Interfaces\CallableResolverInterface;
use \RuntimeException;

class RouteCallableResolver implements CallableResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function resolve($resolve)
    {
        if (is_callable($resolve)) {
            return $resolve;
        }

        if (!is_array($resolve) || count($resolve) < 2) {
            throw new RuntimeException(sprintf('%s is not resolvable', json_encode($resolve)));
        }

        $className = $resolve[0];
        $method = $resolve[1] . 'Action';

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('Callable %s does not exist', $className));
        }

        $callable = [new $className($this->container), $method];

        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf('%s:%s is not callable', $className, $method));
        }

        return $callable;
    }
}