<?php
namespace GameX\Core\Auth;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Permissions\Manager;

class ViewExtension extends Twig_Extension {

    const ACCESS_LIST = [
        'list' => Manager::ACCESS_LIST,
        'view' => Manager::ACCESS_VIEW,
        'create' => Manager::ACCESS_CREATE,
        'edit' => Manager::ACCESS_EDIT,
        'delete' => Manager::ACCESS_DELETE,
    ];

	/**
	 * @var UserModel|null
	 */
	protected $user;

    /**
     * @var bool
     */
	protected $isRootUser = false;

	/**
	 * ViewExtention constructor.
	 * @param Sentinel $auth
	 * @param int $rootId
	 */
	public function __construct(Sentinel $auth, $rootId = null) {
		$this->user = $auth->getUser();
		$this->isRootUser = $this->user
            ? ((int)$this->user->id === $rootId)
            : false;
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
		return (bool) $this->user;
	}

	/**
	 * @return string
	 */
	public function getUserName() {
		return $this->user
			? $this->user->getUserLogin()
			: '';
	}
    
    /**
     * @param string $group
     * @return bool
     */
    public function hasAccessToGroup($group) {
        if (!$this->user) {
            return false;
        }
        return !$this->isRootUser
            ? $this->user->hasAccessToGroup($group)
            : true;
	}
    
    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToPermission($group, $permission, $access = null) {
        if (!$this->user) {
            return false;
        }
        return !$this->isRootUser
            ? $this->user->hasAccessToPermission($group, $permission, $this->getAccess($access))
            : true;
    }
    
    /**
     * @param string $group
     * @param string $permission
     * @param int $resource
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToResource($group, $permission, $resource, $access = null) {
        if (!$this->user) {
            return false;
        }
        return !$this->isRootUser
            ? $this->user->hasAccessToResource($group, $permission, $resource, $this->getAccess($access))
            : true;
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
                $val = isset(self::ACCESS_LIST[$val]) ? self::ACCESS_LIST[$val] : 0;
            }

            $result |= $val;
        }

        return $result;
    }
}
