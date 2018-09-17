<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Tokens extends Migration {
    
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('token')
                    ->nullable()
                    ->after('avatar');
            });
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->dropColumn('token');
            });
    }
}
