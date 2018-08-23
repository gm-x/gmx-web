<?php
namespace GameX\Core\Auth\Interfaces;

interface PermissionsInterface {
    
    /**
     * @param string $group
     * @return bool
     */
    public function hasAccessToGroup($group);

    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToPermission($group, $permission, $access = null);

    /**
     * @param string $group
     * @param string $permission
     * @param int $resource
     * @param int|null $access
     * @return bool
     */
    public function hasAccessToResource($group, $permission, $resource, $access = null);
}
