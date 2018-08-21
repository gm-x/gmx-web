<?php
namespace GameX\Core\Auth\Models;

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
 * @property array $permissions
 * @property \DateTime $completed_at
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class RoleModel extends BaseModel implements RoleInterface, PermissibleInterface {

	use PermissibleTrait;

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
		return $this->hasMany(UserModel::class, 'role_id');
	}

	/**
	 * Get mutator for the "permissions" attribute.
	 *
	 * @param  mixed  $permissions
	 * @return array
	 */
	public function getPermissionsAttribute($permissions) {
		return $permissions ? json_decode($permissions, true) : [];
	}

	/**
	 * Set mutator for the "permissions" attribute.
	 *
	 * @param  mixed  $permissions
	 * @return void
	 */
	public function setPermissionsAttribute(array $permissions) {
		$this->attributes['permissions'] = $permissions ? json_encode($permissions) : '';
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
	 * @param $permissions
	 * @return bool
	 */
	public function hasAccess($permissions) {
		return $this->getPermissionsInstance()->hasAccess($permissions);
	}

	/**
	 * @param $permissions
	 * @return bool
	 */
	public function hasAnyAccess($permissions) {
		return $this->getPermissionsInstance()->hasAnyAccess($permissions);
	}

	protected function createPermissions() {
		return new PermissionsModel(null, $this->permissions);
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
}
