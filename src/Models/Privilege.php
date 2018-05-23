<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Group
 * @package GameX\Models
 *
 * @property integer $player_id
 * @property integer $group_id
 * @property string $prefix
 * @property \DateTime expired_at
 * @property bool active
 * @property Group $group
 * @property Player $player
 */
class Privilege extends BaseModel {

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
	protected $fillable = ['player_id', 'group_id', 'prefix', 'expired_at', 'active'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function group() {
	    return $this->belongsTo(Group::class, 'id', 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function player() {
	    return $this->hasOne(Player::class, 'id', 'player_id');
    }
}
