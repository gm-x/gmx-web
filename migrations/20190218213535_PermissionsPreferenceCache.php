<?php

use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\PreferencesConstants;

class PermissionsPreferenceCache extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        PermissionsModel::create([
            'group' => PreferencesConstants::PERMISSION_GROUP,
            'key' => PreferencesConstants::PERMISSION_UPDATE_KEY,
            'type' => PreferencesConstants::PERMISSION_TYPE,
        ]);
        
        PermissionsModel::create([
            'group' => PreferencesConstants::PERMISSION_GROUP,
            'key' => PreferencesConstants::PERMISSION_CACHE_KEY,
            'type' => PreferencesConstants::PERMISSION_TYPE,
        ]);
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        PermissionsModel::where([
            'group' => PreferencesConstants::PERMISSION_GROUP,
            'key' => PreferencesConstants::PERMISSION_UPDATE_KEY,
            'type' => PreferencesConstants::PERMISSION_TYPE,
        ])->delete();
        
        PermissionsModel::where([
            'group' => PreferencesConstants::PERMISSION_GROUP,
            'key' => PreferencesConstants::PERMISSION_CACHE_KEY,
            'type' => PreferencesConstants::PERMISSION_TYPE,
        ])->delete();
    }
}
