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
			$table->string('steamid', 26);
			$table->string('nick', 32)->default('');
			$table->unsignedTinyInteger('is_steam')->default('0');
			$table->enum('auth_type', [
				'steamid',
				'steamid_pass',
				'nick_pass',
				'steamid_hash',
				'nick_hash',
			])->default('steamid');
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
	return 'players';
}
}
