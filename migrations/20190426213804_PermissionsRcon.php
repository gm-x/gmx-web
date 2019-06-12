<?php

use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\ServersConstants;

class PermissionsRcon extends Migration
{
	const DATA = [
		'group' => ServersConstants::PERMISSION_RCON_GROUP,
		'key' => ServersConstants::PERMISSION_RCON_KEY,
		'type' => ServersConstants::PERMISSION_RCON_TYPE,
	];

	/**
	 * Do the migration
	 */
	public function up() {
		PermissionsModel::create(self::DATA);
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		PermissionsModel::where(self::DATA)->delete();
	}
}
