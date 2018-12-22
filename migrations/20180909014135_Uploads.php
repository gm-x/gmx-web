<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Uploads extends Migration {
    
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedTinyInteger('owner_id')->references('id')->on('users');
                $table->string('filename', 255);
                $table->string('path', 255);
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
        return 'uploads';
    }
}
