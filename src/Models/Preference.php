<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Preference
 * @package GameX\Models
 *
 * @property string $key
 * @property array $value
 * @property Server[] $servers
 */
class Preference extends BaseModel
{
    
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
    protected $fillable = ['key', 'value'];
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var bool
     */
    public $incrementing = false;
    
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
        $this->attributes['value'] = $value ? json_encode($value) : '';
    }
}
