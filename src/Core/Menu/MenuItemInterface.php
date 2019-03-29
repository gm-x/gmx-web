<?php


namespace GameX\Core\Menu;


interface MenuItemInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getRoute();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return null|string
     */
    public function getPermission();

    /**
     * @return null|string
     */
    public function getIcon();

    /**
     * @return MenuItemInterface[]
     */
    public function getItems();

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key);
}