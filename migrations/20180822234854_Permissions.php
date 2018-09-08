<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Permissions extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->enum('group', [
                    'user',
                    'admin',
                ]);
                $table->string('key', 255);
                $table->string('type', 64)->nullable();
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
        return 'permissions';
    }
}
