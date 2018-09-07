<?php
namespace GameX\Core\Auth;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Auth\Models\UserModel;

class ViewExtension extends Twig_Extension {

    const ACCESS_LIST = [
        'list' => Permissions::ACCESS_LIST,
        'view' => Permissions::ACCESS_VIEW,
        'create' => Permissions::ACCESS_CREATE,
        'edit' => Permissions::ACCESS_EDIT,
        'delete' => Permissions::ACCESS_DELETE,
    ];

	/**
	 * @var Sentinel
	 */
	protected $auth;

    /**
     * @var Permissions
     */
	protected $permissions;

    /**
     * @var UserModel
     */
	protected $user;

	/**
	 * ViewExtention constructor.
	 * @param Sentinel $auth
	 * @param Permissions $permissions
	 */
	public function __construct(Sentinel $auth, Permissions $permissions) {
		$this->auth = $auth;
		$this->permissions = $permissions;
		$this->user = $auth->getUser();
	}

	/**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'is_guest',
                [$this, 'isGuest']
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
    public function isGuest() {
		return $this->auth->guest();
	}

	/**
	 * @return string
	 */
	public function getUserName() {
		return !$this->isGuest()
			? $this->user->getUserLogin()
			: '';
	}
    
    /**
     * @param string $group
     * @return bool
     */
    public function hasAccessToGroup($group) {
        return $this->hasAccess('hasAccessToGroup', [$group]);
	}
    
    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToPermission($group, $permission, $access = null) {
        return $this->hasAccess('hasAccessToPermission', [$group, $permission, $this->getAccess($access)]);
    }
    
    /**
     * @param string $group
     * @param string $permission
     * @param int $resource
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToResource($group, $permission, $resource, $access = null) {
        return $this->hasAccess('hasAccessToResource', [$group, $permission, $resource, $this->getAccess($access)]);
    }

    /**
     * @param string $method
     * @param array $args
     * @return bool
     */
    protected function hasAccess($method, array $args) {
        if ($this->isGuest()) {
            return false;
        }

        if ($this->permissions->isRootUser($this->user)) {
            return true;
        }

        if (!$this->user->role) {
            return false;
        }

        return call_user_func_array([$this->permissions, $method], $args);
    }

    /**
     * @param int|string|int[]|string[]|null $access
     * @return int|null
     */
    protected function getAccess($access) {
        if ($access === null) {
            return null;
        }

        if (!is_array($access)) {
            $access = [$access];
        }

        $result = 0;
        foreach ($access as $val) {
            if (!is_numeric($val)) {
                $val = array_key_exists($val, self::ACCESS_LIST) ? self::ACCESS_LIST[$val] : 0;
            }

            $result |= $val;
        }

        return $result;
    }
}
