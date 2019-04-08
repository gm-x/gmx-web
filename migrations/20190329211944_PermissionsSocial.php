<?php

use \GameX\Core\Migration;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Constants\Admin\PreferencesConstants;

class PermissionsSocial extends Migration
{
	const DATA = [
		'group' => PreferencesConstants::PERMISSION_GROUP,
		'key' => PreferencesConstants::PERMISSION_SOCIAL_KEY,
		'type' => PreferencesConstants::PERMISSION_TYPE,
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
