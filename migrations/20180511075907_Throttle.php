<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Throttle extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id')->nullable();
				$table->string('type', 255);
				$table->string('ip', 255)->nullable();
				$table->timestamps();

				$table->index('user_id');
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
		return 'throttle';
	}
}
