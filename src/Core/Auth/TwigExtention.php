<?php
namespace GameX\Core\Auth;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Roles\RoleInterface;

class TwigExtention extends Twig_Extension {

    /**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'role_has_access',
                [$this, 'hasAccess']
            ),
        ];
    }

    public function roleHasAccess(RoleInterface $role, $permission) {
        return $role->hasAccess($permission);
    }
}
