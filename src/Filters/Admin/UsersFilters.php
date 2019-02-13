<?php

namespace GameX\Filters\Admin;

use \GameX\Core\Filters\Filter;
use \GameX\Core\Filters\Fields\Sort;

class UsersFilters
{
    protected $filter;
    
    public function __construct()
    {
        $this->filter = new Filter();
    }
    
    public function create()
    {
        $this->filter->add(new Sort());
    }
}
