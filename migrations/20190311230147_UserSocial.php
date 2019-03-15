<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class UserSocial extends Migration
{
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedTinyInteger('user_id')->references('id')->on('users');
                $table->string('provider', 64);
                $table->string('identifier')->unique();
                $table->string('photo_url', 255);
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
        return 'users_social';
    }
}
