<?php
namespace GameX\Core\AccessFlags;

use \Twig_Extension;
use \Twig_SimpleFunction;

class ViewExtension extends Twig_Extension {

    /**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'get_flags',
                [$this, 'getFlags']
            ),
        ];
    }

    public function getFlags($flags) {
        return Helper::getFlags($flags);
    }
}
