<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class ServerCommands extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getSchema()
            ->create('server_commands', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('server_id')->references('id')->on('servers');
                $table->string('command');
                $table->string('data')->nullable();
                $table->enum('status', [
                    'active',
                    'inactive',
                ])->default('active');
                $table->timestamps();
            });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getSchema()->drop('server_commands');
    }
}
