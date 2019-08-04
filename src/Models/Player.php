<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;
use \GameX\Core\Auth\Models\UserModel;
use \Illuminate\Database\Eloquent\Builder;
use \Carbon\Carbon;

/**
 * Class Player
 * @package GameX\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $emulator
 * @property string $steamid
 * @property string $nick
 * @property string $ip
 * @property string $auth_type
 * @property string $password
 * @property int $access
 * @property string $prefix
 * @property int $server_id
 * @property UserModel $user
 * @property Privilege[] $privileges
 * @property Punishment[] $punishments
 * @property PlayerSession[] $sessions
 * @property PlayerPreference[] $preferences
 * @property Server $server
 */
class Player extends BaseModel
{
    
    const AUTH_TYPE_STEAM = 'steamid';
    const AUTH_TYPE_STEAM_AND_PASS = 'steamid_pass';
    const AUTH_TYPE_NICK_AND_PASS = 'nick_pass';
    const AUTH_TYPE_STEAM_AND_HASH = 'steamid_hash';
    const AUTH_TYPE_NICK_AND_HASH = 'nick_hash';
    
    const ACCESS_RESERVE_NICK = 1;
    const ACCESS_BLOCK_CHANGE_NICK = 2;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'players';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'steamid', 'emulator', 'nick', 'ip', 'auth_type', 'password', 'access', 'prefix'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

	/**
	 * @var array
	 */
	protected $casts = [
		'user_id' => 'int',
		'emulator' => 'int',
	];
    
    /**
     * @var array
     */
    protected $hidden = ['updated_at'];
    
    public static function boot() {
        parent::boot();
        
        Player::deleting(function(Player $player) {
            $player->privileges()->delete();
            $player->punishments()->delete();
            $player->sessions()->delete();
        });
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function privileges()
    {
        return $this->hasMany(Privilege::class, 'player_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function punishments()
    {
        return $this->hasMany(Punishment::class, 'player_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(PlayerSession::class, 'player_id', 'id');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function preferences()
	{
		return $this->hasMany(PlayerPreference::class, 'player_id', 'id');
	}
    
    /**
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = !empty($value) ? md5($value) : null;
    }
    
    /**
     * @param int $access
     * @return bool
     */
    public function hasAccess($access)
    {
        return ($this->access & $access) === $access;
    }
    
    /**
     * @return PlayerSession|null
     */
    public function getActiveSession()
    {
        return $this
            ->sessions()
            ->select('*')
            ->where('status', '=', PlayerSession::STATUS_ONLINE)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
    
    /**
     * @param Server $server
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getActivePunishments(Server $server)
    {
        return $this->punishments()
	        ->select('punishments.*')
	        ->with('reason')
	        ->leftJoin('reasons', 'punishments.reason_id', '=', 'reasons.id')
	        ->where('status', '=', Punishment::STATUS_PUNISHED)
	        ->where(function (Builder $query) {
                $query
	                ->where('expired_at', '>', Carbon::now())
	                ->orWhereNull('expired_at');
            })->where(function (Builder $query) use ($server) {
                $query
	                ->where('reasons.overall', 1)
	                ->orWhere(function (Builder $query) use ($server) {
                        $query->where([
                            'reasons.overall' => 0,
                            'punishments.server_id' => $server->id
                        ]);
                    });
            })->get();
    }
    
    /**
     * @param string $filter
     * @return Player
     */
    public static function filterCollection($filter)
    {
        return self::where('steamid', 'LIKE', '%' . $filter . '%')->orWhere('nick', 'LIKE', '%' . $filter . '%');
    }
    
    /**
     * @return bool
     */
    public function getIsAuthByNick()
    {
        return ($this->auth_type === Player::AUTH_TYPE_NICK_AND_PASS || $this->auth_type === Player::AUTH_TYPE_NICK_AND_HASH);
    }
}
