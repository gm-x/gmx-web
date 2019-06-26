<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Prefix extends Migration
{
	/**
	 * Do the migration
	 */
	public function up() {
		$schema = $this->getSchema();
		$schema->table('privileges', function (Blueprint $table) {
			$table->dropColumn('prefix');
		});
		$schema->table('groups', function (Blueprint $table) {
			$table->string('prefix', 64)
				->nullable()
				->after('priority');
		});
		$schema->table('players', function (Blueprint $table) {
			$table->string('prefix', 64)
				->nullable()
				->after('access');
		});
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		$schema = $this->getSchema();
		$schema->table('privileges', function (Blueprint $table) {
			$table->string('prefix')
				->nullable()
				->after('group_id');
		});
		$schema->table('groups', function (Blueprint $table) {
			$table->dropColumn('prefix');
		});
		$schema->table('players', function (Blueprint $table) {
			$table->dropColumn('prefix');
		});
	}
}
