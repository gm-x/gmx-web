<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;
use \Carbon\Carbon;

/**
 * Class PlayerSession
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $player_id
 * @property integer $server_id
 * @property string $status
 * @property Carbon $disconnected_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Player $player
 * @property Server $server
 */
class PlayerSession extends BaseModel
{
    
    const STATUS_ONLINE = 'online';
    const STATUS_OFFLINE = 'offline';
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'players_sessions';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['player_id', 'server_id', 'status', 'disconnected_at'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'disconnected_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }

	/**
	 * @return bool
	 */
	public function getOnlineAttribute()
	{
		// TODO: remove from this part of code
		Carbon::setLocale('ru');
		return $this->disconnected_at !== null
			? $this->disconnected_at->diffForHumans($this->created_at, true, true, 3)
			: null;
	}
}
