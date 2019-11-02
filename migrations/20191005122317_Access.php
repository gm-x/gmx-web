<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Access extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
	    $this->getSchema()
		    ->create('access', function (Blueprint $table) {
			    $table->increments('id');
			    $table->unsignedInteger('server_id');
			    $table->string('key', 64);
			    $table->string('description');
			    $table->timestamps();
			    $table->unique(['server_id', 'key']);
		    });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    $this->getSchema()->drop('access');
    }
}
