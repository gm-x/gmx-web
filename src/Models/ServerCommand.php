<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class ServerCommand
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $server_id
 * @property string $data
 * @property string $status
 */
class ServerCommand extends BaseModel
{

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'server_commands';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['server_id', 'command', 'data', 'status'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['server_id', 'status', 'created_at', 'updated_at'];

	/**
	 * @var array
	 */
    protected $casts = [
    	'server_id' => 'int',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }
}
