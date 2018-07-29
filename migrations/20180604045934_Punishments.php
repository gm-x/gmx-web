<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Punishments extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('player_id')->references('id')->on('players');
				$table->unsignedInteger('punisher_id')->references('id')->on('players');
				$table->unsignedInteger('server_id')->references('id')->on('servers');
				$table->unsignedInteger('reason_id')->references('id')->on('reasons');
				$table->string('comment', 250);
				$table->unsignedTinyInteger('type');
				$table->timestamp('expired_at')->nullable();
				$table->enum('status', [
					'none',
					'punished',
					'expired',
					'amnestied',
				])->default('none');
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
		return 'punishments';
	}
}
