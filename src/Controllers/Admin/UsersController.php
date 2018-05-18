<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\BaseController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \Exception;

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
		/** @var UserModel $role */
		$user = $this->userRepository->findById($args['user']);

		/** @var RoleModel[] $roles */
		$rolesCollection = $this->getContainer('auth')->getRoleRepository()->all();

		$roles = [];
		foreach ($rolesCollection as $role) {
			$roles[$role->slug] = $role->name;
		}

		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_users_edit');
		$form
			->setAction($this->pathFor('admin_users_edit', ['user' => $user->id]))
			->add('role', $user->role->name, [
				'type' => 'select',
				'title' => 'Role',
				'error' => 'Required',
				'required' => true,
				'values' => $roles,
			], ['required', 'trim', 'min_length' => 1])
			->processRequest();

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
}
