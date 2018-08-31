<?php
namespace GameX\Models;

use \Carbon\Carbon;
use \GameX\Core\BaseModel;

/**
 * @property integer $id
 * @property string $name
 * @property string $ip
 * @property integer $port
 * @property string $token
 * @property string $rcon
 * @property bool $active
 * @property int $num_players
 * @property int $max_players
 * @property int $map_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Group[] $groups
 * @property Reason[] $reasons
 * @property Map $map
 * @property Player[] $players
 */
class Server extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'servers';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['name', 'ip', 'port', 'token', 'rcon', 'active', 'num_players', 'max_players', 'map_id'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['rcon', 'token', 'created_at', 'updated_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups() {
        return $this->hasMany(Group::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reasons() {
        return $this->hasMany(Reason::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function map() {
        return $this->belongsTo(Map::class, 'map_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function players() {
        return $this->hasMany(Player::class, 'server_id');
    }
    
    /**
     * @return string
     */
    public function generateNewToken() {
        $tries = 0;
        do {
            $token = bin2hex(random_bytes(32));
        } while (++$tries < 3 && Server::where('token', $token)->exists());
        return $token;
    }
}
