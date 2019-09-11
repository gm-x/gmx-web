<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class PlayerPreferences extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
	    $this->getSchema()
		    ->create($this->getTableName(), function (Blueprint $table) {
			    $table->unsignedInteger('player_id')->references('id')->on('players');
			    $table->unsignedInteger('server_id')->references('id')->on('servers');
			    $table->text('data');
			    $table->unique(['player_id', 'server_id']);
		    });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    $this->getSchema()->drop($this->getTableName());
    }

	/**
	 * @return string
	 */
	private function getTableName()
	{
		return 'players_preferences';
	}
}
