<?php

namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Twig_Extension;
use \Twig_SimpleFunction;
use \Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Auth\Social\SocialAuth;
use \GameX\Core\Auth\Models\UserModel;

class ViewExtension extends Twig_Extension
{
    const ACCESS_LIST = [
        'list' => Permissions::ACCESS_LIST,
        'view' => Permissions::ACCESS_VIEW,
        'create' => Permissions::ACCESS_CREATE,
        'edit' => Permissions::ACCESS_EDIT,
        'delete' => Permissions::ACCESS_DELETE,
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ViewExtention constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'is_user_active',
                [$this, 'isUserActive']
            ),
            new Twig_SimpleFunction(
                'is_guest',
                [$this, 'isGuest']
            ),
            new Twig_SimpleFunction(
                'get_user_name',
                [$this, 'getUserName']
            ),
            new Twig_SimpleFunction(
                'get_user_avatar',
                [$this, 'getUserAvatar']
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
	        new Twig_SimpleFunction(
		        'social_get_title',
		        [$this, 'socialGetTitle']
	        ),
	        new Twig_SimpleFunction(
		        'social_get_icon',
		        [$this, 'socialGetIcon']
	        ),
        ];
    }

    /**
     * @param UserModel|null $user
     * @return bool
     */
    public function isUserActive($user = null)
    {
        return $this->getAuth()->getActivationRepository()->completed($user ?: $this->getUser());
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->getAuth()->guest();
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return !$this->isGuest()
            ? $this->getUser()->getUserLogin()
            : '';
    }

    /**
     * @return int
     */
    public function getUserAvatar()
    {
        return !$this->isGuest()
            ? ($this->getUser()->avatar ?: '')
            : '';
    }

    /**
     * @param $group
     * @return bool
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function hasAccessToGroup($group)
    {
        return $this->getPermissions()->hasUserAccessToGroup($group);
    }

    /**
     * @param $group
     * @param $permission
     * @param null $access
     * @return bool
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function hasAccessToPermission($group, $permission, $access = null)
    {
        return $this->getPermissions()->hasUserAccessToPermission($group, $permission, $this->getAccess($access));
    }

    /**
     * @param $group
     * @param $permission
     * @param $resource
     * @param null $access
     * @return bool
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function hasAccessToResource($group, $permission, $resource, $access = null)
    {
        return $this->getPermissions()->hasUserAccessToResource($group, $permission, $resource, $this->getAccess($access));
    }

	/**
	 * @param string $key
	 * @return string|null
	 */
    public function socialGetTitle($key)
    {
    	return $this->getSocial()->getTitle($key);
    }

	/**
	 * @param string $key
	 * @return string|null
	 */
    public function socialGetIcon($key)
    {
    	return $this->getSocial()->getIcon($key);
    }

    /**
     * @param int|string|int[]|string[]|null $access
     * @return int|null
     */
    protected function getAccess($access)
    {
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

    /**
     * @return Sentinel
     */
    protected function getAuth()
    {
        return $this->container->get('auth');
    }

    /**
     * @return Permissions
     */
    protected function getPermissions()
    {
        return $this->container->get('permissions');
    }

    /**
     * @return UserModel
     */
    protected function getUser()
    {
        return $this->getAuth()->getUser();
    }

	/**
	 * @return SocialAuth
	 */
    protected function getSocial()
    {
    	return $this->container->get('social');
    }
}
