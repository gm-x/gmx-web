<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Servers extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('ip', 32);
                $table->integer('port');
                $table->string('rcon', 128)->nullable();
                $table->unsignedTinyInteger('active')->default('1');
                $table->string('token', 255)->nullable();
                $table->unsignedTinyInteger('num_players')->default(0);
                $table->unsignedTinyInteger('max_players')->default(0);
                $table->string('map_id')->references('id')->on('maps')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()->drop($this->getTableName());
    }

    /**
     * @return string
     */
    private function getTableName() {
        return 'servers';
    }
}
