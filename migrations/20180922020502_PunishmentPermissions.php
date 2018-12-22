<?php
use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\PunishmentsConstants;

class PunishmentPermissions extends Migration {
    
    /**
     * Do the migration
     */
    public function up() {
        PermissionsModel::create([
            'group' => PunishmentsConstants::PERMISSION_GROUP,
            'key' => PunishmentsConstants::PERMISSION_KEY,
            'type' => PunishmentsConstants::PERMISSION_TYPE,
        ]);
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        PermissionsModel::where([
            'group' => PunishmentsConstants::PERMISSION_GROUP,
            'key' => PunishmentsConstants::PERMISSION_KEY,
            'type' => PunishmentsConstants::PERMISSION_TYPE,
        ])->delete();
    }
}
