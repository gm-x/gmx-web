<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Group
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $player_id
 * @property integer $punisher_id
 * @property string $reason
 * @property integer $type
 * @property string $expired_at
 * @property Player $player
 * @property Player $punisher
 */
class Punishment extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'punishments';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['player_id', 'punisher_id', 'reason', 'type', 'expired_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function player() {
		return $this->belongsTo(Player::class, 'player_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function punisher() {
		return $this->belongsTo(Player::class, 'punisher_id', 'id');
	}
}
