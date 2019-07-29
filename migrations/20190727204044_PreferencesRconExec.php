<?php

use \GameX\Core\Migration;
use \GameX\Models\Preference;
use \GameX\Constants\PreferencesConstants;

class PreferencesRconExec extends Migration
{
	/**
	 * Do the migration
	 */
	public function up() {
		Preference::create([
			'key' => PreferencesConstants::CATEGORY_CRON,
			'value' => [
				'reload_admins' => false
			]
		]);
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		Preference::where('key', PreferencesConstants::CATEGORY_CRON)->delete();
	}
}
