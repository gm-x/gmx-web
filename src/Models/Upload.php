<?php
namespace GameX\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;
use \GameX\Core\Auth\Models\UserModel;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $filename
 * @property string $path
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property UserModel $owner
 */
class Upload extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'uploads';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['owner_id', 'filename', 'path'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function owner() {
	    return $this->hasOne(UserModel::class, 'owner_id', 'id');
    }
}
