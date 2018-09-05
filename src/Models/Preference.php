<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property Server[] $servers
 */
class Preference extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'preferences';

	/**
	 * @var string
	 */
	protected $primaryKey = 'key';

	/**
	 * @var array
	 */
	protected $fillable = ['value'];
}
