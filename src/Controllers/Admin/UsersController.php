<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Forms\Elements\FormSelect;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \Slim\Exception\NotFoundException;
use \Exception;

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

		/** @var RoleModel[] $roles */
		$rolesCollection = $this->getContainer('auth')->getRoleRepository()->all();

		$roles = [];
		foreach ($rolesCollection as $role) {
			$roles[$role->slug] = $role->name;
		}

		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_users_edit');
		$form
			->setAction((string)$request->getUri())
			->add(new FormSelect('role', $user->role->slug, $roles, [
				'title' => 'Role',
				'error' => 'Required',
				'required' => true,
				'empty_option' => 'Choose role'
			]))
			->setRules('role', ['required', 'trim', 'in' => array_keys($roles)])
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$roleHelper = new RoleHelper($this->container);
					$roleHelper->assignUser(
						$form->getValue('role'),
						$user
					);
					return $this->redirect('admin_users_list');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/users/form.twig', [
			'form' => $form,
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
