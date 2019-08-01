<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class PlayerSession
 * @package GameX\Models
 *
 * @property integer $player_id
 * @property integer $server_id
 * @property array $data
 * @property Player $player
 * @property Server $server
 */
class PlayerPreference extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'players_preferences';
    
    /**
     * @var string
     */
    protected $primaryKey = ['player_id', 'server_id'];
    
    /**
     * @var array
     */
    protected $fillable = ['player_id', 'server_id', 'data'];

	protected $casts = [
		'player_id' => 'int',
		'server_id' => 'int',
	];
    
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
	 * @param $value
	 * @return mixed|null
	 */
	public function getDataAttribute($value)
	{
		return $value ? json_decode($value, true) : [];
	}

	/**
	 * @param array $value
	 */
	public function setDataAttribute(array $value)
	{
		$this->attributes['data'] = $value ? json_encode($value) : '';
	}
}
