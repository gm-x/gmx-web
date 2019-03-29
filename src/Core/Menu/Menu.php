<?php

namespace GameX\Core\Menu;

use \Iterator;
use \Psr\Container\ContainerInterface;
use \GameX\Core\Auth\Permissions;

class Menu implements Iterator
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var integer
     */
    protected $position = 0;

    /**
     * @var string
     */
    protected $active;

    /**
     * @var MenuItem[]
     */
    protected $items = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->position = 0;
    }

    /**
     * @param string $route
     * @return $this
     */
    public function setActiveRoute($route)
    {
        $this->active = $route;
        return $this;
    }

    /**
     * @param MenuItemInterface $item
     * @return bool
     */
    public function isActive(MenuItemInterface $item)
    {
        if ($item->getType() === 'item') {
            return $this->active === $item->getRoute();
        }

        foreach ($item->getItems() as $subItem) {
            if ($this->isActive($subItem)) {
                return true;
            }
        }
        return false;
    }

    public function hasAccess(MenuItemInterface $item)
    {
        if (!$item->getPermission()) {
            return true;
        }

        $permissions = $item->getPermission();
        return $this->getPermissions()->hasUserAccessToPermission($permissions[0], $permissions[1]);
    }

    /**
     * @param MenuItemInterface $item
     * @return $this
     */
    public function add(MenuItemInterface $item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    /**
     * @return Permissions
     */
    protected function getPermissions()
    {
        return $this->container->get('permissions');
    }
}
