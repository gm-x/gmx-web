<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class ServerPingAt extends Migration {
	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->table('servers', function (Blueprint $table) {
				$table->timestamp('ping_at')
					->nullable()
					->after('map_id');
			});
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		$this->getSchema()
			->table('servers', function (Blueprint $table) {
				$table->dropColumn('ping_at');
			});
	}
}
