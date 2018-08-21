<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Pagination\Pagination;
use \GameX\Forms\Admin\UsersForm;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \Slim\Exception\NotFoundException;

class UsersController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_users_list';
	}

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
		$user = $this->getUserFromRequest($request, $response, $args);
        $roleHelper = new RoleHelper($this->container);
    
        $form = new UsersForm($user, $roleHelper);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_users_edit', [
                'user' => $user->id,
            ]);
        }

		return $this->render('admin/users/form.twig', [
			'form' => $form->getForm(),
		]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return UserInterface
     * @throws NotFoundException
     */
    protected function getUserFromRequest(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $user = $this->userRepository->findById($args['user']);
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        return $user;
    }
}
