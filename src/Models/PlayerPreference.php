<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;
use \Illuminate\Database\Eloquent\Builder;

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

	/**
	 * @var array
	 */
	protected $casts = [
		'player_id' => 'int',
		'server_id' => 'int',
		'data' => 'json'
	];

	/**
	 * @var array
	 */
	protected $dates = [];

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

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
	 * Set the keys for a save update query.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	protected function setKeysForSaveQuery(Builder $query)
	{
		return $query->where([
			'player_id' => $this->getAttribute('player_id'),
			'server_id' => $this->getAttribute('server_id'),
		]);
	}
}
