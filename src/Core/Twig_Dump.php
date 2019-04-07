<?php

namespace GameX\Core;

use \Twig_Extension;
use \Twig_SimpleFunction;

class Twig_Dump extends Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('dump', [$this, 'dump']),
        ];
    }
    
    public function dump($var)
    {
        return dump($var);
    }
}
