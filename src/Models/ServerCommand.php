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
 * @property boolean $delivered
 */
class ServerCommand extends BaseModel
{
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
    protected $fillable = ['server_id', 'command', 'data', 'delivered'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['server_id', 'delivered', 'created_at', 'updated_at'];

	/**
	 * @var array
	 */
    protected $casts = [
    	'server_id' => 'int',
    	'delivered' => 'bool',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }

    /**
     * @param Server $server
     * @param string $command
     * @param string|null $args
     * @return ServerCommand
     */
    public static function createCommand(Server $server, $command, $args = null)
    {
        $model = new ServerCommand();
        $model->fill([
            'server_id' => $server->id,
            'command' => $command,
            'data' => $args,
            'delivered' => false,
        ]);
        $model->save();
        return $model;
    }
}
