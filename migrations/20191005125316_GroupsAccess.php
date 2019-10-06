<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class GroupsAccess extends Migration
{
	/**
	 * Do the migration
	 */
	public function up()
	{
		$this->getSchema()
			->create('groups_access', function (Blueprint $table) {
				$table->unsignedInteger('group_id');
				$table->unsignedInteger('access_id');
				$table->unique(['group_id', 'access_id']);
			});
	}

	/**
	 * Undo the migration
	 */
	public function down()
	{
		$this->getSchema()->drop('groups_access');
	}
}
