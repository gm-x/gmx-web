<?php
namespace GameX\Core\Auth\Middlewares;

use \Slim\Http\Request;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToPermission extends BaseMiddleware {
    
    /**
     * @var string
     */
    protected $group;
    
    /**
     * @var string
     */
    protected $permission;
    
    /**
     * @var int|null
     */
    protected $access;
    
    /**
     * @param string $group
     * @param string $permission
     * @param int|null $access
     */
    public function __construct($group, $permission, $access = null) {
        $this->group = $group;
        $this->permission = $permission;
        $this->access = $access;
    }
    
    /**
     * @inheritdoc
     */
    protected function checkAccess(Request $request, UserModel $user) {
        return $user->hasAccessToPermission($this->group, $this->permission, $this->access);
    }
}
