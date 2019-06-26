<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Privilege
 * @package GameX\Models
 *
 * @property integer $player_id
 * @property integer $group_id
 * @property \DateTime expired_at
 * @property bool active
 * @property Group $group
 * @property Player $player
 */
class Privilege extends BaseModel
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'privileges';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['player_id', 'group_id', 'expired_at', 'active'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'expired_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['server_id', 'player_id', 'created_at', 'updated_at'];

	/**
	 * @var array
	 */
	protected $casts = [
		'player_id' => 'int',
		'group_id' => 'int',
		'active' => 'int',
	];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }
}
