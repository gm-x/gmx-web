<?php

namespace GameX\Core\Auth\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Auth\Models\UserModel;

class HasAccessToGroup {
    
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
        
        if (!$user->hasAccessToGroup($this->group)) {
            throw new NotAllowedException();
        }
        
        return $next($request, $response);
    }
}
