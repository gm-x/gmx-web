<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Core\Auth\Models\RoleModel;

class Permissions extends CacheItem {

    /**
     * @inheritdoc
     */
    protected function getData($element) {
        $data = [];
        /** @var RoleModel $role */
        foreach (RoleModel::get() as $role) {
            $data[$role->getKey()] = $this->getRolePermissions($role);
        }
        return $data;
    }
    
    /**
     * @param RoleModel $role
     * @return array
     */
    protected function getRolePermissions(RoleModel $role) {
        $result = [
            'groups' => [],
            'permissions' => [],
            'resources' => [],
        ];
    
        /** @var \GameX\Core\Auth\Models\RolesPermissionsModel[] $permissions */
        $permissions = $role->permissions()->with('permission')->get();
    
        foreach ($permissions as $permission) {
            $p = $permission->permission;
            if ($p->type === null) {
                if (!array_key_exists($p->group, $result['permissions'])) {
                    $result['permissions'][$p->group] = [];
                }
                $result['permissions'][$p->group][$p->key] = $permission->access;
            } else {
                if (!array_key_exists($p->group, $result['resources'])) {
                    $result['resources'][$p->group] = [];
                }
                if (!array_key_exists($p->key, $result['resources'][$p->group])) {
                    $result['resources'][$p->group][$p->key] = [];
                }
                $result['resources'][$p->group][$p->key][$permission->resource] = $permission->access;
            }
        
            if ($permission->access > 0) {
                $result['groups'][$p->group] = true;
            }
        }
        
        return $result;
    }
}
