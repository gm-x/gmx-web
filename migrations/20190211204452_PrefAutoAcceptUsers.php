<?php
use \GameX\Core\Migration;
use \GameX\Models\Preference;

class PrefAutoAcceptUsers extends Migration
{
    /**
     * Do the migration
     */
    public function up() {
        $element = Preference::where('key', 'main')->first();
        $value = $element->value;
        $value['auto_activate_users'] = true;
        $element->value = $value;
        $element->save();
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        $element = Preference::where('key', 'main')->first();
        $value = $element->value;
        unset($value['auto_activate_users']);
        $element->value = $value;
        $element->save();
    }
}
