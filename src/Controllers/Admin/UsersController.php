<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class UsersController extends BaseController {
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/users/index.twig');
    }
}
