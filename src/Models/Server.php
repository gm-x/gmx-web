<?php

namespace GameX\Models;

use \Illuminate\Database\Eloquent\SoftDeletes;
use \GameX\Core\BaseModel;
use \Carbon\Carbon;
use \GameX\Core\Utils;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property string $type
 * @property string $name
 * @property string $ip
 * @property integer $port
 * @property string $token
 * @property string $rcon
 * @property bool $active
 * @property int $num_players
 * @property int $max_players
 * @property int $map_id
 * @property Carbon $ping_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Group[] $groups
 * @property Reason[] $reasons
 * @property Map $map
 * @property Player[] $players
 * @property PlayerSession[] $sessions
 * @property bool $online
 */
class Server extends BaseModel
{
    use SoftDeletes;
    
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
    protected $fillable = ['type', 'name', 'ip', 'port', 'token', 'rcon', 'active', 'num_players', 'max_players', 'map_id', 'ping_at'];
    
    /**
     * @var array
     */
    protected $dates = ['ping_at', 'created_at', 'updated_at', 'deleted_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['rcon', 'token', 'created_at', 'updated_at', 'deleted_at'];

	/**
	 * @var array
	 */
	protected $appends = ['online'];

	/**
	 * @var array
	 */
	protected $casts = [
		'port' => 'int',
		'num_players' => 'int',
		'max_players' => 'int',
		'map_id' => 'int',
	];

	protected $isOnline = null;

	/**
	 * @return bool
	 */
	public function getOnlineAttribute()
	{
		if ($this->isOnline === null) {
			$this->isOnline = $this->ping_at !== null
				&& $this->ping_at->diffInMinutes(Carbon::now(), false) < 3;
		}

		return $this->isOnline;
	}
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'server_id');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function access()
	{
		return $this->hasMany(Access::class, 'server_id');
	}
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reasons()
    {
        return $this->hasMany(Reason::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function map()
    {
        return $this->belongsTo(Map::class, 'map_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function players()
    {
        return $this->hasMany(Player::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(PlayerSession::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Relations\HasMany[]
     */
    public function getActiveSessions()
    {
        return $this
            ->sessions()
            ->with('player')
            ->where('status', PlayerSession::STATUS_ONLINE)
//	        ->whereTime('updated_at', '>', Carbon::today())
	        ->groupBy('id', 'player_id')
            ->orderBy('created_at', 'DESC')
            ->limit($this->max_players)
            ->get();
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    public function generateNewToken()
    {
        $tries = 0;
        do {
            $token = Utils::generateToken(32);
        } while (++$tries < 3 && Server::where('token', $token)->exists());
        return $token;
    }
}
