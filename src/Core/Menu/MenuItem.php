<?php

namespace GameX\Core\Menu;

class MenuItem implements MenuItemInterface
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string|null
     */
    protected $permission = null;

    /**
     * @var string|null
     */
    protected $icon = null;

    /**
     * @param string $title
     * @param string $route
     * @param array $params
     * @param string|null $permission
     * @param string|null $icon
     */
    public function __construct($title, $route, array $params = [], $permission = null, $icon = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->params = $params;
        $this->permission = $permission;
        $this->icon = $icon;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function __get($key)
    {
        switch ($key) {
            case 'title':
                {
                    return $this->getTitle();
                }

            case 'route':
                {
                    return $this->getRoute();
                }

            case 'params':
                {
                    return $this->getParams();
                }

            case 'permission':
                {
                    return $this->getPermission();
                }

            case 'icon':
                {
                    return $this->getIcon();
                }

            default:
                {
                    return null;
                }
        }
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
        return $this->route;
    }

    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return $this->permission;
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
    public function getItems()
    {
        return [];
    }
}
