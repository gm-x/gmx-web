<?php

use \Phpmig\Migration\Migration;
use \Illuminate\Database\Schema\Builder;
use \Illuminate\Database\Schema\Blueprint;

class Servers extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 256);
                $table->string('ip', 32);
                $table->integer('port');
                $table->string('rcon', 128);
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
     * @return Builder
     */
    protected function getSchema() {
        return $this->container['db']->schema();
    }

    /**
     * @return string
     */
    private function getTableName() {
        return 'servers';
    }
}
