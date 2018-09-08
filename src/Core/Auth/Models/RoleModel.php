<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \Cartalyst\Sentinel\Roles\RoleInterface;
use \GameX\Core\BaseModel;

/**
 * Class RoleModel
 * @package GameX\Core\Auth\Models
 *
 * @property int $id
 * @property string $name
 * @property Carbon $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property UserModel[] $users
 * @property RolesPermissionsModel[] $permissions
 */
class RoleModel extends BaseModel {

    /**
     * @var array|null
     */
    protected $cachedPermissions = null;

	/**
	 * @var string
	 */
	protected $table = 'roles';

	/**
	 * @var array
	 */
	protected $fillable = ['name'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

	/**
	 * The Users relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function users() {
		return $this->hasMany(UserModel::class, 'role_id', 'id');
	}

	/**
	 * The Users relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function permissions() {
		return $this->hasMany(RolesPermissionsModel::class, 'role_id', 'id');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoleId() {
		return $this->getKey();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUsers() {
		return $this->users;
	}

    /**
     * @return array
     */
	protected function getPermissionsList() {
	    if ($this->cachedPermissions === null) {
            $this->cachedPermissions = [];
	        /** @var RolesPermissionsModel[] $permissions */
	        $permissions = $this->permissions()->with('permission')->get();
	        foreach ($permissions as $permission) {
	            $p = $permission->permission;
	            if (!array_key_exists($p->group, $this->cachedPermissions)) {
	                $this->cachedPermissions[$p->group] = [];
                }
	            $this->cachedPermissions[$p->group][$p->key] = $permission->access;
            }
        }
        
        return $this->cachedPermissions;
    }
}
