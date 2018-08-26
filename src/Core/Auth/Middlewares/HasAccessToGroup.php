<?php
namespace GameX\Core\Auth\Middlewares;

use \Slim\Http\Request;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToGroup extends BaseMiddleware {
    
    /**
     * @var string
     */
    protected $group;
    
    /**
     * @param string $group
     */
    public function __construct($group) {
        $this->group = $group;
    }
    
    /**
     * @inheritdoc
     */
    protected function checkAccess(Request $request, UserModel $user) {
        return $user->hasAccessToGroup($this->group);
    }
}
