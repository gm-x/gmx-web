<?php

namespace GameX\Core\Filters\Fields;

class Sort
{
    protected $value;
    
    public function __construct($value = null)
    {
        $this->setValue($value);
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function getTemplate()
    {
        return 'sort';
    }
}
