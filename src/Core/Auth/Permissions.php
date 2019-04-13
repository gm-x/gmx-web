<?php
namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Cache\Cache;
use \GameX\Core\Lang\Language;
use \GameX\Core\FlashMessages;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Exceptions\RoleNotFoundException;
use \GameX\Core\Cache\NotFoundException;

class Permissions {

    const GROUP_USER = 'user';
    const GROUP_ADMIN = 'admin';

    const ACCESS_LIST= 1;
    const ACCESS_VIEW = 2;
    const ACCESS_CREATE = 4;
    const ACCESS_EDIT = 8;
    const ACCESS_DELETE = 16;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Closure
     */
    protected $isAuthorizedCallable = null;

    /**
     * @var int
     */
    protected $rootUserId = null;
    
    /**
     * @var PermissionsModel[]|null
     */
    protected $permissions = null;
    
    /**
     * @var int
     */
    protected $cachedRole = 0;
    
    /**
     * @var array
     */
    protected $cachedGroups = [];
    
    /**
     * @var array
     */
    protected $cachedResources = [];

	/**
	 * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    public function isRootUser(UserModel $user) {
    	if ($this->rootUserId === null) {
		    /** @var Node $config */
		    $config = $this->container->get('config')->getNode('permissions');
		    $this->rootUserId = $config->get('root_user');
	    }
        return ($user->id === $this->rootUserId);
    }

	/**
	 * @param RoleModel $role
	 * @param $group
	 * @return bool
	 * @throws NotFoundException
	 * @throws RoleNotFoundException
	 */
    public function hasAccessToGroup(RoleModel $role, $group) {
        $permissions = $this->cacheRole($role);
        return array_key_exists($group, $permissions['groups']) && $permissions['groups'][$group];
    }

	/**
	 * @param RoleModel $role
	 * @param $group
	 * @param $permission
	 * @param null $access
	 * @return bool
	 * @throws NotFoundException
	 * @throws RoleNotFoundException
	 */
    public function hasAccessToPermission(RoleModel $role, $group, $permission, $access = null) {
        $permissions = $this->cacheRole($role);
        
        if (!array_key_exists($group, $permissions['permissions'])) {
            return false;
        }
    
        if (!array_key_exists($permission, $permissions['permissions'][$group])) {
            return false;
        }
    
        if ($access === null) {
            return true;
        }
    
        return ($permissions['permissions'][$group][$permission] & $access) === $access;
    }

	/**
	 * @param RoleModel $role
	 * @param $group
	 * @param $permission
	 * @param $resource
	 * @param null $access
	 * @return bool
	 * @throws NotFoundException
	 * @throws RoleNotFoundException
	 */
    public function hasAccessToResource(RoleModel $role, $group, $permission, $resource, $access = null) {
        $permissions = $this->cacheRole($role);
    
        if (!array_key_exists($group, $permissions['resources'])) {
            return false;
        }
    
        if (!array_key_exists($permission, $permissions['resources'][$group])) {
            return false;
        }
    
        if (!array_key_exists($resource, $permissions['resources'][$group][$permission])) {
            return false;
        }
    
        if ($access === null) {
            return true;
        }
    
        return ($permissions['resources'][$group][$permission][$resource] & $access) === $access;
    }

    /**
     * @return \Closure
     */
    public function isAuthorizedMiddleware() {
        if ($this->isAuthorizedCallable === null) {
            $self = $this;
            $this->isAuthorizedCallable = function (Request $request, Response $response, callable $next) use ($self) {
                /** @var UserModel|null $user */
                $user = $request->getAttribute('user');
                if (!$user) {
                    return $self->redirectToLogin($response);
                }

                return $next($request, $response);
            };
        }

        return $this->isAuthorizedCallable;
    }

    /**
     * @param string $group
     * @return \Closure
     */
    public function hasAccessToGroupMiddleware($group) {
        return $this->getMiddleware(function (RoleModel $role, array $args) use ($group) {
            return $this->hasAccessToGroup($role, $group);
        });
    }

    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return \Closure
     */
    public function hasAccessToPermissionMiddleware($group, $permission, $access = null) {
        return $this->getMiddleware(function (RoleModel $role, array $args) use ($group, $permission, $access) {
            return $this->hasAccessToPermission($role, $group, $permission, $access);
        });
    }

    /**
     * @param string $key
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return \Closure
     */
    public function hasAccessToResourceMiddleware($key, $group, $permission, $access = null) {
        return $this->getMiddleware(function (RoleModel $role, array $args) use ($key, $group, $permission, $access) {
            return array_key_exists($key, $args)
                ? $this->hasAccessToResource($role, $group, $permission, $args[$key], $access)
                : false;
        });
    }
    
    /**
     * @param string $group
     * @return bool
     * @throws RoleNotFoundException
     */
    public function hasUserAccessToGroup($group) {
    	$role = $this->getUserRole();
    	return ($role instanceof RoleModel)
		    ? $this->hasAccessToGroup($role, $group)
		    : $role;
    }
    
    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     * @throws RoleNotFoundException
     */
    public function hasUserAccessToPermission($group, $permission, $access = null) {
	    $role = $this->getUserRole();
	    return ($role instanceof RoleModel)
		    ? $this->hasAccessToPermission($role, $group, $permission, $access)
		    : $role;
    }
    
    /**
     * @param string $group
     * @param string $permission
     * @param string $resource
     * @param int|null $access
     * @return bool
     * @throws RoleNotFoundException
     */
    public function hasUserAccessToResource($group, $permission, $resource, $access = null) {
	    $role = $this->getUserRole();
	    return ($role instanceof RoleModel)
		    ? $this->hasAccessToResource($role, $group, $permission, $resource, $access)
		    : $role;
    }

	/**
	 * @param RoleModel $role
	 * @return mixed
	 * @throws NotFoundException
	 * @throws RoleNotFoundException
	 */
    protected function cacheRole(RoleModel $role) {
        $key = $role->getKey();

	    /** @var Cache $cache */
	    $cache = $this->container->get('cache');
	    $cachedPermissions = $cache->get('permissions');

        if (!array_key_exists($key, $cachedPermissions)) {
            throw new RoleNotFoundException('Role ' . $role->id . ' was not found');
        }
        
        return $cachedPermissions[$key];
    }

    /**
     * @param callable $handler
     * @return \Closure
     */
    protected function getMiddleware(callable $handler) {
        $self = $this;
        return function (Request $request, Response $response, callable $next) use ($self, $handler) {
            /** @var UserModel|null $user */
            $user = $request->getAttribute('user');
            if (!$user) {
                return $self->redirectToLogin($response);
            }

            if ($self->isRootUser($user)) {
                return $next($request, $response);
            }

            if (!$user->role) {
                throw new NotAllowedException();
            }

            list (, , $args) = $request->getAttribute('routeInfo');
            if (!$handler($user->role, $args)) {
                throw new NotAllowedException();
            }

            return $next($request, $response);
        };
    }

    /**
     * @param Response $response
     * @return Response
     */
    protected function redirectToLogin(Response $response) {
        /** @var Language $lang */
        $lang = $this->container->get('lang');
        /** @var FlashMessages $flashMessage */
        $flashMessage = $this->container->get('flash');
        $flashMessage->addMessage('error', $lang->format('labels', 'login_redirect'));
        /** @var Router $router */
        $router = $this->container->get('router');
        return $response->withRedirect($router->pathFor('login'));
    }

	/**
	 * @return bool|RoleModel
	 */
    protected function getUserRole() {
    	/** @var UserModel|bool $user */
	    $user = $this->container->get('auth')->check();
	    if (!$user) {
		    return false;
	    }

	    if ($this->isRootUser($user)) {
		    return true;
	    }

	    if (!$user->role_id) {
		    return false;
	    }

	    return $user->role;
    }
}
