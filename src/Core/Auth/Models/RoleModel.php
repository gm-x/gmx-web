<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Roles\EloquentRole;
use \GameX\Core\Auth\SentinelBootstrapper;

/**
 * Class RoleModel
 * @package GameX\Core\Auth\Models
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array $permissions
 * @property \DateTime $completed_at
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class RoleModel extends EloquentRole {
	/**
	 * The Users relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function users() {
		return $this->hasMany(SentinelBootstrapper::USER_MODEL, 'role_id');
	}
}
