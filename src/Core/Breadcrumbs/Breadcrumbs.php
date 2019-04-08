<?php

namespace GameX\Core\Breadcrumbs;

use \Iterator;
use \Countable;

class Breadcrumbs implements Iterator, Countable
{
    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * @param string $title
     * @param string|null $url
     * @return $this
     */
    public function add($title, $url = null)
    {
        $this->breadcrumbs[] = [
            'title' => $title,
            'url' => $url
        ];
        return $this;
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