<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class UserStatus extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->enum('status', [
                    'pending',
                    'active',
                    'banned',
                ])->default('pending');
            });
    }

    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()
            ->table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
    }
}
