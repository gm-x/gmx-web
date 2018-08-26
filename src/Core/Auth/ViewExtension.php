<?php
namespace GameX\Core\Auth;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Sentinel;

class ViewExtension extends Twig_Extension {

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
                'has_access_group',
                [$this, 'hasAccessToGroup']
            ),
            new Twig_SimpleFunction(
                'has_access_permission',
                [$this, 'hasAccessToPermission']
            ),
            new Twig_SimpleFunction(
                'has_access_resource',
                [$this, 'hasAccessToResource']
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
     * @param string $group
     * @return bool
     */
    public function hasAccessToGroup($group) {
        /** @var \GameX\Core\Auth\Models\UserModel $user */
        $user = $this->auth->getUser();
        return $user
            ? $user->hasAccessToGroup($group)
            : false;
	}
    
    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToPermission($group, $permission, $access = null) {
        /** @var \GameX\Core\Auth\Models\UserModel $user */
        $user = $this->auth->getUser();
        return $user
            ? $user->hasAccessToPermission($group, $permission, $access)
            : false;
    }
    
    /**
     * @param string $group
     * @param string $permission
     * @param int $resource
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToResource($group, $permission, $resource, $access = null) {
        /** @var \GameX\Core\Auth\Models\UserModel $user */
        $user = $this->auth->getUser();
        return $user
            ? $user->hasAccessToResource($group, $permission, $resource, $access)
            : false;
    }
}
