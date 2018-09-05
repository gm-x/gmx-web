<?php
use \GameX\Core\Migration;
use \GameX\Models\Preference;

class PreferencesList extends Migration
{
    /**
     * Do the migration
     */
    public function up() {
        foreach ($this->getList() as $item) {
            Preference::create($item);
        }
    }

    /**
     * Undo the migration
     */
    public function down() {
        foreach ($this->getList() as $item) {
            Preference::where($item)->delete();
        }
    }

    /**
     * @return array
     */
    protected function getList() {
        return [];
    }
}
