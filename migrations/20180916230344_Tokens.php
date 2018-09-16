<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Tokens extends Migration {
    
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()->create(
                $this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 32);
                $table->unsignedTinyInteger('resource')->nullable();
                $table->string('token')->unique();
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();
            }
            );
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
        return 'tokens';
    }
}
