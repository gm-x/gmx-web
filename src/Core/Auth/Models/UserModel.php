<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Users\EloquentUser;
use \GameX\Core\Auth\SentinelBootstrapper;
use \Cartalyst\Sentinel\Permissions\StrictPermissions;

/**
 * Class UserModel
 * @package GameX\Core\Auth\Models
 * @property int $id
 * @property string $email
 * @property string $password
 * @property array $permissions
 * @property int $role_id
 * @property \DateTime $last_login
 * @property \DateTime $created_at
 * @property \DateTime $update_at
 */
class UserModel extends EloquentUser {
	protected $fillable = [
		'email',
		'password',
		'last_name',
		'first_name',
		'permissions',
		'role_id'
	];

	// TODO: Make refactoring for it
	public function roles() {
		return parent::roles();
	}

	/**
	 * @return RoleModel
	 */
	public function role() {
		return $this->belongsTo(SentinelBootstrapper::ROLE_MODEL)->first();
	}

	// TODO: Make refactoring for it
	public function inRole($role) {
		return parent::inRole($role);
	}

	/**
	 * Creates a permissions object.
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	protected function createPermissions() {
		return new StrictPermissions(null, $this->role()->permissions);
	}
}
