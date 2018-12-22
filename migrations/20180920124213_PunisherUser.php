<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class PunisherUser extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('punishments', function (Blueprint $table) {
                $table->unsignedTinyInteger('punisher_user_id')
                    ->nullable()
                    ->after('punisher_id');
            });
    }

    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('punishments', function (Blueprint $table) {
                $table->dropColumn('punisher_id');
            });
    }
}
