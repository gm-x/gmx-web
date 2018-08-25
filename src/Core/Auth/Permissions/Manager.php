<?php
namespace GameX\Core\Auth\Permissions;

use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;

class Manager {
    const GROUP_USER = 'user';
    const GROUP_ADMIN = 'admin';

    const ACCESS_LIST= 1;
    const ACCESS_VIEW = 2;
    const ACCESS_CREATE = 4;
    const ACCESS_EDIT = 8;
    const ACCESS_DELETE = 16;
    
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
}
