<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Persistences\PersistenceInterface;
use \GameX\Core\BaseModel;

/**
 * Class PersistenceModel
 * @package GameX\Core\Auth\Models
 * @property
 */
class PersistenceModel extends BaseModel implements PersistenceInterface {

	/**
	 * @var string
	 */
	protected $table = 'persistences';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'code',
        'created_at',
        'updated_at'
    ];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(UserModel::class);
	}
}
