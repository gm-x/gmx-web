<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \GameX\Core\BaseController;
use GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class UsersController extends BaseController {

    /** @var  UserRepositoryInterface */
    protected $userRepository;

    public function init() {
        $this->userRepository = $this->getContainer('auth')->getUserRepository();
    }

    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $pagination = new Pagination($this->userRepository->get(), $request);
        return $this->render('admin/users/index.twig', [
            'users' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }

    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        //
    }
}
