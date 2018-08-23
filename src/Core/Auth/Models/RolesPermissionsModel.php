<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;

/**
 * @property integer $id
 * @property integer $role_id
 * @property integer $permission_id
 * @property integer $resource
 * @property integer $access
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
}
