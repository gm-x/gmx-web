<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Group
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $server_id
 * @property string $title
 * @property integer $flags
 * @property integer $priority
 * @property Server $server
 * @property Privilege[] $players
 */
class Group extends BaseModel
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['server_id', 'title', 'flags', 'priority'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['server_id', 'created_at', 'updated_at'];

	/**
	 * @var array
	 */
    protected $casts = [
    	'flags' => 'int',
    	'priority' => 'int',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function players()
    {
        return $this->hasMany(Privilege::class, 'group_id');
    }
}
