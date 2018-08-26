<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Roles extends Migration {
	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->string('slug', 255)->unique();
				$table->string('name', 255);
				$table->timestamp('completed_at')->nullable();
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
		return 'roles';
	}
}
