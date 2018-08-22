<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;

/**
 * @property integer $id
 * @property string $group
 * @property string $key
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property RolesPermissionsModel[] $roles
 */
class PermissionsModel extends BaseModel {
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['group', 'key'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles() {
        return $this->hasMany(RoleModel::class, 'role_id', 'id');
    }
//
//	/**
//	 * @return array
//	 */
//	protected function createPreparedPermissions() {
//		$prepared = [];
//
//		foreach ($this->secondaryPermissions as $keys => $value) {
//			foreach ($this->extractClassPermissions($keys) as $key) {
//				// If the value is not in the array, we're opting in
//				if (! array_key_exists($key, $prepared)) {
//					$prepared[$key] = $value;
//
//					continue;
//				}
//
//				// If our value is in the array and equals false, it will override
//				if ($value === false) {
//					$prepared[$key] = $value;
//				}
//			}
//		}
//
//		return $prepared;
//	}
//
//    /**
//     * {@inheritDoc}
//     */
//    public function updatePermission($permission, $value = true, $create = false) {
//        if (array_key_exists($permission, $this->permissions)) {
//            $permissions = $this->permissions;
//
//            $permissions[$permission] = $value;
//
//            $this->permissions = $permissions;
//        } elseif ($create) {
//            $this->addPermission($permission, $value);
//        }
//
//        return $this;
//    }
}
