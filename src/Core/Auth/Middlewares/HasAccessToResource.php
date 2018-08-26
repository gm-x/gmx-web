<?php
namespace GameX\Core\Auth\Middlewares;

use \Slim\Http\Request;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToResource extends BaseMiddleware {
    
    /**
     * @var string
     */
    protected $key;
    
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
     * @param string $key
     * @param string $group
     * @param string $permission
     * @param int|null $access
     */
    public function __construct($key, $group, $permission, $access = null) {
        $this->key = $key;
        $this->group = $group;
        $this->permission = $permission;
        $this->access = $access;
    }
    
    /**
     * @inheritdoc
     */
    protected function checkAccess(Request $request, UserModel $user) {
        list (, , $args) = $request->getAttribute('routeInfo');
        if (!array_key_exists($this->key, $args)) {
            return false;
        }
        
        return $user->hasAccessToResource($this->group, $this->permission, $args[$this->key], $this->access);
    }
}
