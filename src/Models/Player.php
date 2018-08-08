<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;
use \GameX\Core\Auth\Models\UserModel;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $emulator
 * @property string $steamid
 * @property string $nick
 * @property string $auth_type
 * @property string $password
 * @property integer $access
 * @property UserModel $user
 * @property Privilege[] $privileges
 * @property Punishment[] $punishments
 */
class Player extends BaseModel {

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
	protected $fillable = ['user_id', 'steamid', 'emulator', 'nick', 'auth_type', 'password', 'access'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function user() {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function privileges() {
        return $this->hasMany(Privilege::class, 'player_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function punishments() {
        return $this->hasMany(Punishment::class, 'player_id', 'id');
    }

	/**
	 * @param $value
	 */
	public function setPasswordAttribute($value) {
		$this->attributes['password'] = !empty($value) ? md5($value) : null;
	}

    /**
     * @param int $access
     * @return bool
     */
    public function hasAccess($access) {
        return ($this->access & $access) === $access;
    }

    /**
     * @param string $filter
     * @return Player
     */
	public static function filterCollection($filter) {
		return self::where('steamid', 'LIKE', '%' . $filter . '%')
            ->orWhere('nick', 'LIKE', '%' . $filter . '%');
	}
}
