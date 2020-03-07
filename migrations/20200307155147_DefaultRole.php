<?php
use \GameX\Core\Migration;
use \GameX\Models\Preference;
use \GameX\Constants\PreferencesConstants;

class DefaultRole extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Preference::updateOrCreate([
            'key' => PreferencesConstants::CATEGORY_ROLES,
        ], [
            'key' => PreferencesConstants::CATEGORY_ROLES,
            'value' => [
                'default' => 0
            ]
        ]);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Preference::where('key', PreferencesConstants::CATEGORY_ROLES)->delete();
    }
}
