<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Players extends Migration {

	/**
	 * Do the migration
	 */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable()->references('id')->on('users');
                $table->string('steamid', 26);
                $table->unsignedTinyInteger('emulator')->default('0');
                $table->string('nick', 32)->default('');
                $table->enum('auth_type', [
                    'steamid',
                    'steamid_pass',
                    'nick_pass',
                    'steamid_hash',
                    'nick_hash',
                ])->default('steamid');
                $table->ipAddress('ip');
                $table->string('password', 255)->nullable();
                $table->unsignedInteger('access')->default('0');
                $table->unsignedInteger('server_id')->nullable()->references('id')->on('servers');
                $table->timestamps();
                
                $table->unique(['steamid', 'emulator'], 'steamid_idx');
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
        return 'players';
    }
}
