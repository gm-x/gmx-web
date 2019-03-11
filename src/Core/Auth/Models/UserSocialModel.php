<?php
namespace GameX\Core\Auth\Models;

use \GameX\Core\BaseModel;

class UserSocialModel extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'users_social';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}