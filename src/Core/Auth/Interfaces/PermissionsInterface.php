<?php
namespace GameX\Core\Auth\Interfaces;

interface PermissionsInterface {

    /**
     * @param string $group
     * @param string|null $permission
     * @param int|null $access
     * @param int $serverId
     * @return bool
     */
    public function hasAccess($group, $permission = null, $access = null, $serverId = 0);
}