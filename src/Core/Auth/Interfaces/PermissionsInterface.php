<?php
namespace GameX\Core\Auth\Interfaces;

interface PermissionsInterface {

    /**
     * @param string $group
     * @param string|null $permission
     * @param int|null $access
     * @return bool
     */
    public function hasAccess($group, $permission = null, $access = null, $serverId = 0);

    /**
     * @param string $group
     * @param string|null $permission
     * @param int $serverId
     * @param int|null $access
     * @return bool
     */
    public function hasAccessServer($group, $permission = null, $access = null, $serverId = 0);
}