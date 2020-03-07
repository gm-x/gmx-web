<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class PlayerSessionPingAt extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
	    $this->getSchema()->table('players_sessions', function (Blueprint $table) {
		    $table->timestamp('ping_at')
                ->nullable()
			    ->after('disconnected_at');
	    });

    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    $this->getSchema()->table('players_sessions', function (Blueprint $table) {
		    $table->dropColumn('ping_at');
	    });
    }
}
