<?php
namespace GameX\Core\Auth\Permissions\Handlers;

use \GameX\Core\Auth\Permissions\HandleInterface;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Models\RoleModel;

class HasAccessToGroup implements HandleInterface {

    /**
     * @var string
     */
    protected $group;

    /**
     * @param string $group
     */
    public function __construct($group) {
        $this->group = $group;
    }

    /**
     * @inheritdoc
     */
    public function checkAccess(Manager $manager, RoleModel $role, array $args) {
        return $manager->hasAccessToGroup($role, $this->group);
    }
}