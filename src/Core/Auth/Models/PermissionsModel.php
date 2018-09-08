<?php
namespace GameX\Core\Auth\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;

/**
 * @property integer $id
 * @property string $group
 * @property string $key
 * @property string|null $type
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
    protected $fillable = ['group', 'key', 'type'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles() {
        return $this->hasMany(RoleModel::class, 'role_id', 'id');
    }
}
