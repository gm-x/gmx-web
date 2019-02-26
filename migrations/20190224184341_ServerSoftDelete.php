<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class ServerSoftDelete extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('servers', function (Blueprint $table) {
                $table->softDeletes();
            });
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('servers', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
    }
}
