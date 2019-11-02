<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;
use \Illuminate\Database\Eloquent\Builder;

/**
 * Class GroupAccess
 * @package GameX\Models
 *
 * @property integer $group_id
 * @property integer $access_id
 */
class GroupAccess extends BaseModel
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups_access';
    
    /**
     * @var string
     */
    protected $primaryKey = ['group_id', 'access_id'];

    /**
     * @var array
     */
    protected $fillable = ['group_id', 'access_id'];

	/**
	 * @var array
	 */
	protected $casts = [
		'group_id' => 'int',
		'access_id' => 'int',
	];

	/**
	 * Set the keys for a save update query.
	 *
	 * @param  Builder  $query
	 * @return Builder
	 */
	protected function setKeysForSaveQuery(Builder $query) {
		return $query->where([
			'group_id' => $this->getAttribute('group_id'),
			'access_id' => $this->getAttribute('access_id'),
		]);
	}
}
