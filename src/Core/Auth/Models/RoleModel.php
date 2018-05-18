<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Roles\EloquentRole;
use \GameX\Core\Auth\SentinelBootstrapper;

class RoleModel extends EloquentRole {
	/**
	 * The Users relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function users()
	{
		return $this->hasMany(SentinelBootstrapper::USER_MODEL, 'role_id');
	}
}
