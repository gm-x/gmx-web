<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class UserAvatar extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('avatar')
                    ->nullable()
                    ->after('last_login');
            });
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
    }
}
