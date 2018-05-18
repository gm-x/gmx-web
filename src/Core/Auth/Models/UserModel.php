<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Users\EloquentUser;
use \GameX\Core\Auth\SentinelBootstrapper;

class UserModel extends EloquentUser {
	protected $fillable = [
		'email',
		'password',
		'last_name',
		'first_name',
		'permissions',
		'role_id'
	];

	public function roles() {}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role() {
		return $this->belongsTo(SentinelBootstrapper::ROLE_MODEL);
	}
}
