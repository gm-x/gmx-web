<?php

namespace GameX\Core\Auth\Models;

use \GameX\Core\BaseModel;

class ThrottleModel extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'throttle';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'ip',
        'type',
        'user_id'
    ];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}