<?php
namespace GameX\Core\Auth\Middlewares;

use \Slim\Http\Request;
use \GameX\Core\Auth\Models\UserModel;

class IsAuthorized extends BaseMiddleware {
    
    /**
     * @inheritdoc
     */
    protected function checkAccess(Request $request, UserModel $user) {
        return true;
	}
}
