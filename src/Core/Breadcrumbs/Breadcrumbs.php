<?php

namespace GameX\Core\Breadcrumbs;

use \Iterator;
use \Countable;

class Breadcrumbs implements Iterator, Countable
{

    /**
     * @var Breadcrumb[]
     */
    protected $breadcrumbs = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @param string $title
     * @param string $url
     * @return $this
     */
    public function add($title, $url)
    {
        $this->breadcrumbs[] = new Breadcrumb($title, $url);
        return $this;
    }

    /**
     * @return Breadcrumb[]
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->breadcrumbs[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->breadcrumbs[$this->position]);
    }

    public function count()
    {
        return count($this->breadcrumbs);
    }
}