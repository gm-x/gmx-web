<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \Cartalyst\Sentinel\Permissions\PermissibleInterface;
use \Cartalyst\Sentinel\Roles\RoleInterface;
use \Cartalyst\Sentinel\Permissions\PermissibleTrait;
use \GameX\Core\BaseModel;

/**
 * Class RoleModel
 * @package GameX\Core\Auth\Models
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property UserModel[] $users
 * @property RolesPermissionsModel[] $permissions
 */
class RoleModel extends BaseModel implements RoleInterface {
    
    protected $cachedPermissions = null;

	/**
	 * The Eloquent users model name.
	 *
	 * @var string
	 */
	protected static $usersModel = UserModel::class;

	/**
	 * @var string
	 */
	protected $table = 'roles';

	/**
	 * @var array
	 */
	protected $fillable = [
		'name',
		'slug',
		'permissions',
	];

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

//	/**
//	 * Get mutator for the "permissions" attribute.
//	 *
//	 * @param  mixed  $permissions
//	 * @return array
//	 */
//	public function getPermissionsAttribute($permissions) {
//		return $permissions ? json_decode($permissions, true) : [];
//	}
//
//	/**
//	 * Set mutator for the "permissions" attribute.
//	 *
//	 * @param  mixed  $permissions
//	 * @return void
//	 */
//	public function setPermissionsAttribute(array $permissions) {
//		$this->attributes['permissions'] = $permissions ? json_encode($permissions) : '';
//	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoleId() {
		return $this->getKey();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoleSlug() {
		return $this->slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUsers() {
		return $this->users;
	}

	/**
	 * @param $permission
	 * @return bool
	 */
	public function hasAccess($permission) {
	    $this->getPermissionsList();
	    return array_key_exists($permission, $this->cachedPermissions) && $this->cachedPermissions[$permission];
	}

	/**
	 * @param $permissions
	 * @return bool
	 */
	public function hasAnyAccess($permissions) {
	    foreach ($permissions as $permission) {
	        if ($this->hasAccess($permission)) {
	            return true;
            }
        }
        
        return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getUsersModel()
	{
		return static::$usersModel;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function setUsersModel($usersModel)
	{
		static::$usersModel = $usersModel;
	}
	
	
	protected function getPermissionsList() {
	    if ($this->cachedPermissions === null) {
	        /** @var RolesPermissionsModel[] $permissions */
	        $permissions = $this->permissions()->with('permission')->get();
	        foreach ($permissions as $permission) {
	            $this->cachedPermissions[$permission->permission->key] = true;
            }
        }
        
        return $this->cachedPermissions;
    }
}
