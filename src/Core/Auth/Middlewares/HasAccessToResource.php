<?php

namespace GameX\Core\Auth\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToResource {
    
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
    
        list (, , $args) = $request->getAttribute('routeInfo');
        if (!array_key_exists($this->key, $args)) {
            throw new NotAllowedException();
        }
        
        if (!$user->hasAccessToResource($this->group, $this->permission, $args[$this->key], $this->access)) {
            throw new NotAllowedException();
        }
        
        return $next($request, $response);
    }
}
