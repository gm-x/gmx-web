<?php
use \GameX\Core\Migration;
use \GameX\Models\Preference;

class UkrainianLanguageSupport extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Preference::updateOrCreate([
            'key' => 'languages',
        ], [
            'key' => 'languages',
            'value' => [
                'en' => 'English',
                'ru' => 'Русский',
                'ua' => 'Українська',
                'cz' => 'Česky'
            ]
        ]);
    }

    /**
     * Undo the migration
     */
    public function down() {}
}
