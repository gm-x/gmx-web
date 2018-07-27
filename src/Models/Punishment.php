<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;
use \Carbon\Carbon;

/**
 * Class Group
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $player_id
 * @property integer $punisher_id
 * @property integer $server_id
 * @property string $reason
 * @property integer $type
 * @property string $expired_at
 * @property string $status
 * @property Player $player
 * @property Player $punisher
 */
class Punishment extends BaseModel {

	const STATUS_NONE = 'none';
	const STATUS_PUNISHED = 'punished';
	const STATUS_EXPIRED = 'expired';
	const STATUS_AMNESTIED = 'amnestied';

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
	protected $fillable = ['player_id', 'punisher_id', 'server_id', 'reason', 'type', 'expired_at'];

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

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function server() {
		return $this->belongsTo(Server::class, 'server_id', 'id');
	}

	/**
	 * @return int
	 */
	public function getExpiredAtAttribute($value) {
		return Carbon::parse($value, 'UTC')->getTimestamp();
	}

	public function setExpiredAtAttribute($value) {
	    if ($value === null) {
	        $this->attributes['expired_at'] = null;
        } elseif ($value instanceof \DateTime) {
	        $this->attributes['expired_at'] = $value->format('Y-m-d H:i:s');
        } elseif (is_null($value)) {
            $this->attributes['expired_at'] = Carbon::createFromTimestamp($value, 'UTC')->toDateTimeString();
        } else {
            $this->attributes['expired_at'] = $value;
        }
	}
}
