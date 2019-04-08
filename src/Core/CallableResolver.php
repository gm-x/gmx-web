<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Interfaces\CallableResolverInterface;
use \RuntimeException;

class CallableResolver implements CallableResolverInterface
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

        $className = $resolve;
        $method = null;

        if (is_array($resolve)) {
            if (count($resolve) < 1) {
                throw new RuntimeException(sprintf('%s is not resolvable', json_encode($resolve)));
            }
            $className = $resolve[0];
            $method = count($resolve) > 1 ? $resolve[1] : null;
        }

        $callable = $this->resolveCallable($className, $method);

        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf('%s:%s is not callable', $className, $method));
        }

        return $callable;
    }

    /**
     * Check if string is something in the DIC
     * that's callable or is a class name which has an __invoke() method.
     *
     * @param string $class
     * @param string $method
     * @return callable
     *
     * @throws \RuntimeException if the callable does not exist
     */
    protected function resolveCallable($class, $method)
    {
        if ($this->container->has($class)) {
            return [$this->container->get($class), $method];
        }

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('Callable %s does not exist', $class));
        }

        $object = new $class($this->container);
        if ($method === null) {
            return $object;
        }


        return method_exists($object, $method . 'Action')
            ? [$object , $method . 'Action']
            : [$object, $method];
    }
}