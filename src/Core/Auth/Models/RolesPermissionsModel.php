<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;

/**
 * @property integer $id
 * @property integer $role_id
 * @property integer $permission_id
 * @property string $access
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property RoleModel $role
 * @property PermissionsModel $permission
 */
class RolesPermissionsModel extends BaseModel {
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles_permissions';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['role_id', 'permission_id', 'access'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role() {
        return $this->belongsTo(RoleModel::class, 'role_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission() {
        return $this->belongsTo(PermissionsModel::class, 'permission_id', 'id');
    }

	/**
	 * @return array
	 */
	protected function createPreparedPermissions() {
		$prepared = [];

		foreach ($this->secondaryPermissions as $keys => $value) {
			foreach ($this->extractClassPermissions($keys) as $key) {
				// If the value is not in the array, we're opting in
				if (! array_key_exists($key, $prepared)) {
					$prepared[$key] = $value;

					continue;
				}

				// If our value is in the array and equals false, it will override
				if ($value === false) {
					$prepared[$key] = $value;
				}
			}
		}

		return $prepared;
	}
    
    /**
     * {@inheritDoc}
     */
    public function updatePermission($permission, $value = true, $create = false) {
        if (array_key_exists($permission, $this->permissions)) {
            $permissions = $this->permissions;
            
            $permissions[$permission] = $value;
            
            $this->permissions = $permissions;
        } elseif ($create) {
            $this->addPermission($permission, $value);
        }
        
        return $this;
    }
}
