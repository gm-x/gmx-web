<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Access
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $server_id
 * @property string $key
 * @property string $description
 */
class Access extends BaseModel
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'access';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['server_id', 'key', 'description'];

	/**
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];

	/**
	 * @var array
	 */
	protected $hidden = ['server_id', 'created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function server()
	{
		return $this->belongsTo(Server::class, 'server_id', 'id');
	}
}
