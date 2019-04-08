<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class SocialProfileUrl extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->table('users_social', function (Blueprint $table) {
				$table->string('profile_url')
					->nullable()
					->after('identifier');
			});
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		$this->getSchema()
			->table('users_social', function (Blueprint $table) {
				$table->dropColumn('profile_url');
			});
	}
}
