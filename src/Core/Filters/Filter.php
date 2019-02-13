<?php

namespace GameX\Core\Filters;

class Filter
{
    protected $fields = [];
    
    public function add($filter) {
        $this->fields[] = $filter;
        return $this;
    }
}
