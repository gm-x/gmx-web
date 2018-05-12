<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Cartalyst\Sentinel\Sentinel;

class UsersController extends BaseController {
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        /** @var Sentinel $auth */
        $auth = $this->getContainer('auth');
        $users = $auth->getUserRepository()->get();
        foreach ($users as $user) {
            var_dump($user->email);
        }
        die();

        return $this->render('admin/users/index.twig');
    }
}
