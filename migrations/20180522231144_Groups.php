<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Groups extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('server_id')->references('id')->on('servers');
                $table->string('title', 255);
                $table->unsignedInteger('flags');
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
        return 'groups';
    }
}
