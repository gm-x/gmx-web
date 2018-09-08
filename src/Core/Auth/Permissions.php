<?php
namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Router;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Lang\Language;
use \GameX\Core\FlashMessages;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Exceptions\NotAllowedException;

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
    protected $rootUserId;
    
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
    protected $cachedPermissions = [];
    
    /**
     * @var array
     */
    protected $cachedResources = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;

        /** @var Node $config */
        $config = $container->get('config')->getNode('permissions');
        $this->rootUserId = $config->get('root_user');
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    public function isRootUser(UserModel $user) {
        return ($user->id === $this->rootUserId);
    }

    /**
     * @param RoleModel $role
     * @param string $group
     * @return bool
     */
    public function hasAccessToGroup(RoleModel $role, $group) {
        $this->cacheRole($role);
        return array_key_exists($group, $this->cachedGroups) && $this->cachedGroups[$group];
    }
    
    /**
     * @param RoleModel $role
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToPermission(RoleModel $role, $group, $permission, $access = null) {
        $this->cacheRole($role);
        
        if (!array_key_exists($group, $this->cachedPermissions)) {
            return false;
        }
    
        if (!array_key_exists($permission, $this->cachedPermissions[$group])) {
            return false;
        }
    
        if ($access === null) {
            return true;
        }
    
        return ($this->cachedPermissions[$group][$permission] & $access) === $access;
    }
    
    /**
     * @param RoleModel $role
     * @param string $group
     * @param string $permission
     * @param int $resource
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToResource(RoleModel $role, $group, $permission, $resource, $access = null) {
        $this->cacheRole($role);
    
        if (!array_key_exists($group, $this->cachedResources)) {
            return false;
        }
    
        if (!array_key_exists($permission, $this->cachedResources[$group])) {
            return false;
        }
    
        if (!array_key_exists($resource, $this->cachedResources[$group][$permission])) {
            return false;
        }
    
        if ($access === null) {
            return true;
        }
    
        return ($this->cachedResources[$group][$permission][$resource] & $access) === $access;
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
     * @param RoleModel $role
     */
    protected function cacheRole(RoleModel $role) {
        if ($this->cachedRole == $role->id) {
            return;
        }

        $this->cachedGroups = [];
        $this->cachedPermissions = [];
        $this->cachedResources = [];

        /** @var \GameX\Core\Auth\Models\RolesPermissionsModel[] $permissions */
        $permissions = $role->permissions()->with('permission')->get();

        foreach ($permissions as $permission) {
            $p = $permission->permission;
            if ($p->type === null) {
                if (!array_key_exists($p->group, $this->cachedPermissions)) {
                    $this->cachedPermissions[$p->group] = [];
                }
                $this->cachedPermissions[$p->group][$p->key] = $permission->access;
            } else {
                if (!array_key_exists($p->group, $this->cachedResources)) {
                    $this->cachedResources[$p->group] = [];
                }
                if (!array_key_exists($p->key, $this->cachedResources[$p->group])) {
                    $this->cachedResources[$p->group][$p->key] = [];
                }
                $this->cachedResources[$p->group][$p->key][$permission->resource] = $permission->access;
            }

            if ($permission->access > 0) {
                $this->cachedGroups[$p->group] = true;
            }
        }

        $this->cachedRole = $role->id;
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
}