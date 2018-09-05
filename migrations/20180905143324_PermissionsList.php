<?php
use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;

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
                'group' => 'admin',
                'key' => 'server',
                'type' => NULL,
            ],[
                'group' => 'admin',
                'key' => 'user',
                'type' => NULL,
            ], [
                'group' => 'admin',
                'key' => 'user_role',
                'type' => NULL,
            ], [
                'group' => 'admin',
                'key' => 'role',
                'type' => NULL,
            ], [
                'group' => 'admin',
                'key' => 'player',
                'type' => NULL,
            ], [
                'group' => 'admin',
                'key' => 'role_permission',
                'type' => NULL,
            ], [
                'group' => 'admin',
                'key' => 'server_group',
                'type' => 'server',
            ], [
                'group' => 'admin',
                'key' => 'server_reason',
                'type' => 'server',
            ]
        ];
    }
}


