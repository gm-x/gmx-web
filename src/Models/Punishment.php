<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;
use \GameX\Core\Auth\Models\UserModel;

/**
 * Class Group
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $player_id
 * @property integer $punisher_id
 * @property integer $punisher_user_id
 * @property integer $server_id
 * @property integer $reason_id
 * @property string $details
 * @property integer $type
 * @property string $expired_at
 * @property string $status
 * @property Player $player
 * @property Player $punisher
 * @property Player $punisherUser
 * @property Server $server
 * @property Reason $reason
 * @property bool $permanent
 */
class Punishment extends BaseModel {

    const STATUS_NONE = 'none';
    const STATUS_PUNISHED = 'punished';
    const STATUS_EXPIRED = 'expired';
    const STATUS_AMNESTIED = 'amnestied';
    
    const TYPE_BANNED = 1;
    const TYPE_GAGED = 2;
    const TYPE_MUTED = 4;
    
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
    protected $fillable = ['player_id', 'punisher_id', 'punisher_user_id', 'server_id', 'reason_id', 'details', 'type', 'expired_at', 'status'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'expired_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['reason_id', 'updated_at'];
    
    /**
     * @var array
     */
    protected $appends = ['types', 'time'];

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
	public function punisherUser() {
		return $this->belongsTo(UserModel::class, 'punisher_user_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function server() {
		return $this->belongsTo(Server::class, 'server_id', 'id');
	}
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function reason() {
	    return $this->belongsTo(Reason::class, 'reason_id', 'id');
    }
    
    /**
     * @return int
     */
    public function getTimeAttribute() {
        return $this->attributes['expired_at'] !== null
            ? $this->attributes['expired_at']->diffInSeconds($this->attributes['created_at'])
            : 0;
    }
    
    /**
     * @return bool
     */
	public function getPermanentAttribute() {
	    return $this->attributes['expired_at'] === null;
    }
    
    /**
     * @return array
     */
    public function getTypesAttribute() {
        $types = [];
        if ($this->attributes['type'] && self::TYPE_BANNED) {
            $types[] = 'ban';
        }
        if ($this->attributes['type'] && self::TYPE_GAGED) {
            $types[] = 'gag';
        }
        if ($this->attributes['type'] && self::TYPE_MUTED) {
            $types[] = 'mute';
        }
        return $types;
    }
}
