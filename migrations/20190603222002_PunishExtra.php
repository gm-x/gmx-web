<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class PunishExtra extends Migration
{
	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->table('punishments', function (Blueprint $table) {
				$table->integer('extra')
					->nullable()
					->after('type');
			});
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		$this->getSchema()
			->table('punishments', function (Blueprint $table) {
				$table->dropColumn('extra');
			});
	}
}
