<?php

namespace GameX\Core;

use \Pimple\Container as PimpleContainer;
use \Psr\Container\ContainerInterface;

class Container extends PimpleContainer implements ContainerInterface
{
    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }
}
