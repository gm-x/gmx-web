<?php
namespace GameX\Core\Auth\Permissions;

use GameX\Core\Auth\Models\RoleModel;

interface HandleInterface {

    /**
     * @param Manager $manager
     * @param RoleModel $role
     * @param array $args
     * @return bool
     */
    public function checkAccess(Manager $manager, RoleModel $role, array $args);
}