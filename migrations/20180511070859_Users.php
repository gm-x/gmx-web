<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Users extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->string('login')->unique();
				$table->string('email')->nullable()->unique();
				$table->string('password', 255)->nullable();
                $table->string('token', 70)->nullable();
				$table->unsignedInteger('role_id')->default('0')->references('id')->on('roles');
                $table->unsignedTinyInteger('avatar')->nullable();
                $table->unsignedTinyInteger('is_social')->default('0')->nullable();
				$table->timestamp('last_login')->nullable();
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
		return 'users';
	}
}
