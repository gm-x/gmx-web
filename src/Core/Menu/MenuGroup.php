<?php

namespace GameX\Core\Menu;

class MenuGroup implements MenuItemInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $icon = null;

    /**
     * @var MenuItemInterface[]
     */
    protected $items = [];

    /**
     * @param string $title
     * @param string|null $icon
     */
    public function __construct($title, $icon = null)
    {
        $this->title = $title;
        $this->icon = $icon;
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

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'index';
    }

    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function __get($key)
    {

    }
}