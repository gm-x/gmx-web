<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * @property string $key
 * @property array $value
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

    /**
     * @param string $value
     * @return array|mixed
     */
    public function getValueAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * @param array $value
     */
    public function setValueAttribute(array $value)
    {
        $this->attributes['permissions'] = $value ? json_encode($value) : '';
    }
}
