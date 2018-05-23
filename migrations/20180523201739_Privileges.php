<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Privileges extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('player_id')->references('id')->on('players');
                $table->unsignedInteger('group_id')->references('id')->on('groups');
                $table->string('prefix', 255)->nullable();
                $table->timestamp('expired_at');
                $table->unsignedTinyInteger('active');
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
        return 'privileges';
    }
}
