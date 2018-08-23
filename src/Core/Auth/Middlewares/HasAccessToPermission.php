<?php

namespace GameX\Core\Auth\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToPermission {
    
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        /** @var UserModel|null $user */
        $user = $request->getAttribute('user');
        if (!$user) {
            throw new NotAllowedException();
        }
        
        if (!$user->hasAccessToPermission($this->group, $this->permission, $this->access)) {
            throw new NotAllowedException();
        }
        
        return $next($request, $response);
    }
}
