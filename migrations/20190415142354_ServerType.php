<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class ServerType extends Migration
{
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('servers', function (Blueprint $table) {
                $table->string('type', 32)
                    ->nullable()
                    ->after('id');
            });
    }

    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('servers', function (Blueprint $table) {
                $table->dropColumn('type');
            });
    }
}
