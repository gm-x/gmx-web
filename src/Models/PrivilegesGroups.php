<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class PrivilegesGroups
 * @package GameX\Models
 *
 * @property integer $id
 * @property integer $server_id
 * @property string $title
 * @property integer $flags
 * @property Server $server
 */
class PrivilegesGroups extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'privileges_groups';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['server_id', 'title', 'flags'];

	public function server() {
	    return $this->belongsTo(Server::class, 'id', 'server_id');
    }
}
