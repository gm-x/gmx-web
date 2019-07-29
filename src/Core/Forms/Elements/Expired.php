<?php

namespace GameX\Core\Forms\Elements;

class Expired extends DateTimeInput
{
    protected $format = 'Y-m-d';

    public function getType()
    {
        return 'expired';
    }
}