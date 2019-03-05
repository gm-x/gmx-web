<?php


namespace GameX\Core\Breadcrumbs;


class Breadcrumb
{
    protected $title;
    protected $url;

    public function __construct($title, $url)
    {
        $this->title = $title;
        $this->url = $url;
    }

    protected function getTitle()
    {
        return $this->title;
    }

    protected function getURL()
    {
        return $this->url;
    }
}