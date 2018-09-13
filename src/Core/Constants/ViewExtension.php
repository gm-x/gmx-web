<?php
namespace GameX\Core\Constants;

use \Twig_Extension;
use \Twig_Extension_GlobalsInterface;
use \GameX\Core\Constants\Routes\Admin\Players;
use \GameX\Core\Constants\Routes\Admin\Privileges;
use \GameX\Core\Constants\Routes\Admin\Servers;

class ViewExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface {
    
    protected $routes = [
        'admin' => [
            'players' => Players::class,
            'privileges' => Privileges::class,
            'servers' => Servers::class,
        ],
    ];
    
    public function getGlobals() {
        return [
            'routes' => $this->getConstants($this->routes)
        ];
    }
    
    protected function getConstants(array $list) {
        $result = [];
        foreach ($list as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->getConstants($value);
            } else if (class_exists($value, true)) {
                $class = new \ReflectionClass($value);
                $result[$key] = $class->getConstants();
            } else {
                throw new \Exception('Bad value ' . $value);
            }
        }
        return $result;
    }
}
