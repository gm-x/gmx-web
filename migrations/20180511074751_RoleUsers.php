<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class RoleUsers extends Migration  {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->unsignedInteger('user_id');
				$table->unsignedInteger('role_id');
				$table->timestamps();

				$table->primary(['user_id', 'role_id']);
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
		return 'role_users';
	}
}
