<?php

use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\AccessConstants;

class AccessPermissions extends Migration
{
	const DATA = [
		'group' => AccessConstants::PERMISSION_GROUP,
		'key' => AccessConstants::PERMISSION_KEY,
		'type' => AccessConstants::PERMISSION_TYPE,
	];

	/**
	 * Do the migration
	 */
	public function up()
	{
		PermissionsModel::create(self::DATA);
	}

	/**
	 * Undo the migration
	 */
	public function down()
	{
		PermissionsModel::where(self::DATA)->delete();
	}
}
