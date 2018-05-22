<?php
namespace GameX\Core\Auth;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Roles\RoleInterface;

class ViewExtention extends Twig_Extension {

	/**
	 * @var Sentinel
	 */
	protected $auth;

	/**
	 * ViewExtention constructor.
	 * @param Sentinel $auth
	 */
	public function __construct(Sentinel $auth) {
		$this->auth = $auth;
	}

	/**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'is_user',
                [$this, 'isUser']
            ),
            new Twig_SimpleFunction(
                'get_user_name',
                [$this, 'getUserName']
            ),
            new Twig_SimpleFunction(
                'has_access',
                [$this, 'hasAccess']
            ),
            new Twig_SimpleFunction(
                'role_has_access',
                [$this, 'roleHasAccess']
            ),
        ];
    }

	/**
	 * @return bool
	 */
    public function isUser() {
		return ($this->auth->check() !== false);
	}

	/**
	 * @return string
	 */
	public function getUserName() {
    	$user = $this->auth->getUser();
		return $user
			? $user->getUserLogin()
			: '';
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
    public function hasAccess($permission) {
    	return $this->auth->hasAccess($permission);
	}

	/**
	 * @param RoleInterface $role
	 * @param string $permission
	 * @return bool
	 */
    public function roleHasAccess(RoleInterface $role, $permission) {
        return $role->hasAccess($permission);
    }
}
