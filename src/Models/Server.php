<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property string $name
 * @property string $ip
 * @property integer $port
 * @property PrivilegesGroups[] $groups
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
	protected $fillable = ['name', 'ip', 'port'];

    /**
     * Get the comments for the blog post.
     */
    public function groups()
    {
        return $this->hasMany(PrivilegesGroups::class, 'server_id');
    }
}
