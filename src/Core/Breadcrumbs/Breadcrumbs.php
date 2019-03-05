<?php

namespace GameX\Core\Breadcrumbs;

class Breadcrumbs
{

    /**
     * @var array Breadcrumb
     */
    protected $breadcrumbs = [];

    public function add($title, $url)
    {
        $this->breadcrumbs[] = new Breadcrumb($title, $url);
        return $this;
    }

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }
}