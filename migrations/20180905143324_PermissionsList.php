<?php
use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Constants\Admin\GroupsConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Constants\Admin\UsersConstants;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PermissionsConstants;
use \GameX\Constants\Admin\PreferencesConstants;

class PermissionsList extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        foreach ($this->getList() as $item) {
            PermissionsModel::create($item);
        }
    }

    /**
     * Undo the migration
     */
    public function down() {
        foreach ($this->getList() as $item) {
            PermissionsModel::where($item)->delete();
        }
    }

    /**
     * @return array
     */
    protected function getList() {
        return [
            [
                'group' => ServersConstants::PERMISSION_GROUP,
                'key' => ServersConstants::PERMISSION_KEY,
                'type' => ServersConstants::PERMISSION_TYPE,
            ], [
                'group' => ServersConstants::PERMISSION_TOKEN_GROUP,
                'key' => ServersConstants::PERMISSION_TOKEN_KEY,
                'type' => ServersConstants::PERMISSION_TOKEN_TYPE,
            ], [
                'group' => PlayersConstants::PERMISSION_GROUP,
                'key' => PlayersConstants::PERMISSION_KEY,
                'type' => PlayersConstants::PERMISSION_TYPE,
            ], [
                'group' => PrivilegesConstants::PERMISSION_GROUP,
                'key' => PrivilegesConstants::PERMISSION_KEY,
                'type' => PrivilegesConstants::PERMISSION_TYPE,
            ], [
                'group' => GroupsConstants::PERMISSION_GROUP,
                'key' => GroupsConstants::PERMISSION_KEY,
                'type' => GroupsConstants::PERMISSION_TYPE,
            ], [
                'group' => ReasonsConstants::PERMISSION_GROUP,
                'key' => ReasonsConstants::PERMISSION_KEY,
                'type' => ReasonsConstants::PERMISSION_TYPE,
            ], [
                'group' => UsersConstants::PERMISSION_GROUP,
                'key' => UsersConstants::PERMISSION_KEY,
                'type' => UsersConstants::PERMISSION_TYPE,
            ], [
                'group' => RolesConstants::PERMISSION_GROUP,
                'key' => RolesConstants::PERMISSION_KEY,
                'type' => RolesConstants::PERMISSION_TYPE,
            ], [
                'group' => PermissionsConstants::PERMISSION_GROUP,
                'key' => PermissionsConstants::PERMISSION_KEY,
                'type' => PermissionsConstants::PERMISSION_TYPE,
            ], [
                'group' => PreferencesConstants::PERMISSION_GROUP,
                'key' => PreferencesConstants::PERMISSION_MAIN_KEY,
                'type' => PreferencesConstants::PERMISSION_TYPE,
            ], [
                'group' => PreferencesConstants::PERMISSION_GROUP,
                'key' => PreferencesConstants::PERMISSION_EMAIL_KEY,
                'type' => PreferencesConstants::PERMISSION_TYPE,
            ]
        ];
    }
}


