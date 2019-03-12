<?php
namespace GameX\Core\Auth\Models;

use \GameX\Core\BaseModel;

/**
 * Class UserSocialModel
 * @package GameX\Core\Auth\Models
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $identifier
 * @property string $photo_url
 * @property UserModel $user
 */
class UserSocialModel extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'users_social';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'provider',
        'identifier',
        'photo_url'
    ];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(UserModel::class);
    }
}
