<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \GameX\Core\Forms\Form;

abstract class BaseForm {
    
    /**
     * @var ContainerInterface
     */
    protected static $container;
    
    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) {
        static::$container = $container;
    }
    
    /**
     * @param array $data
     * @return Form
     */
    abstract protected static function init(array $data);
    
    /**
     * @param $name
     * @return Form
     */
    protected static function create($name) {
        return new Form(static::$container->get('session'), static::$container->get('lang'), $name);
    }
}
