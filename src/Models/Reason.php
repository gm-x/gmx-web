<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $server_id
 * @property string $title
 * @property integer $time
 * @property integer $overall
 * @property integer $menu
 * @property integer $active
 * @property Server $server
 * @property Punishment $punishments
 */
class Reason extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'reasons';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['server_id', 'title', 'time', 'overall', 'menu', 'active'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['server_id', 'overall', 'active', 'created_at', 'updated_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function server() {
        return $this->hasMany(Server::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function punishments() {
        return $this->hasMany(Punishment::class, 'reason_id', 'id');
    }
}
