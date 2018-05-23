<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property string $steamid
 * @property string $nick
 * @property boolean $is_steam
 * @property string $auth_type
 * @property string $password
 */
class Player extends BaseModel {

	const AUTH_TYPE_STEAM = 'steamid';
	const AUTH_TYPE_STEAM_AND_PASS = 'steamid_pass';
	const AUTH_TYPE_NICK_AND_PASS = 'nick_pass';
	const AUTH_TYPE_STEAM_AND_HASH = 'steamid_hash';
	const AUTH_TYPE_NICK_AND_HASH = 'nick_hash';

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
	protected $fillable = ['steamid', 'nick', 'is_steam', 'auth_type', 'password'];

	/**
	 * @param $value
	 */
	public function setPasswordAttribute($value) {
		$this->attributes['password'] = md5($value);
	}
}
