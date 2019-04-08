<?php

namespace GameX\Core\Breadcrumbs;

use \ArrayAccess;

class Breadcrumb implements ArrayAccess
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * Breadcrumb constructor.
     * @param string $title
     * @param string $url
     */
    public function __construct($title, $url)
    {
        $this->title = (string) $title;
        $this->url = (string) $url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getURL()
    {
        return $this->url;
    }

    /**
     * @param int|string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {}

    /**
     * @param int|string $offset
     */
    public function offsetUnset($offset) {}

    /**
     * @param int|string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['title', 'url'], true);
    }

    /**
     * @param int|string $offset
     * @return Breadcrumb|mixed|null
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'title': {
                return $this->title;
            }

            case 'url': {
                return $this->url;
            }

            default: {
                return null;
            }
        }
    }
}