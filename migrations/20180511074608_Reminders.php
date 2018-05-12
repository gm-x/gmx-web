<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Reminders extends Migration {
	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->string('code', 255);
				$table->tinyInteger('completed')->default('0');
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
		return 'reminders';
	}
}
