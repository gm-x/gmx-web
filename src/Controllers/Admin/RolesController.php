<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Roles\RoleInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\FormInputText;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\ValidationException;
use \Exception;

class RolesController extends BaseAdminController {
    const PERMISSIONS = [
        'index' => 'Index',
        'admin.users' => 'Admin Users CRUD',
        'admin.roles' => 'Admin Roles CRUD',
        'admin.servers' => 'Admin Servers CRUD',
        'admin.user.role' => 'Admin User Set Role',
        'admin.players' => 'Admin Players Role',
		'admin.servers.groups' => 'Admin Privileges Groups CRUD',
		'admin.players.privileges' => 'Admin Players Privileges CRUD',
    ];

    /** @var  RoleRepositoryInterface */
    protected $roleRepository;

	/**
	 * @return string
	 */
	protected function getActiveAdminMenu() {
		return 'admin_roles_list';
	}

	public function init() {
        $this->roleRepository = $this->getContainer('auth')->getRoleRepository();
    }

    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/roles/index.twig', [
            'roles' => $this->roleRepository->get()
        ]);
    }

    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $form = $this->getForm(new RoleModel())
            ->setAction((string)$request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $roleHelper = new RoleHelper($this->container);
                    $roleHelper->createRole(
                        $form->getValue('name'),
                        $form->getValue('slug')
                    );
                    return $this->redirect('admin_roles_list');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/roles/form.twig', [
            'form' => $form,
            'create' => true,
        ]);
    }

    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        $form = $this->getForm($role)
            ->setAction((string)$request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $role->fill($form->getValues());
                    $role->save();
                    return $this->redirect('admin_roles_list');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/roles/form.twig', [
            'form' => $form,
            'create' => false,
        ]);
    }

    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        try {
            $role->delete();
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }

        return $this->redirect('admin_roles_list');
    }

    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        $pagination = new Pagination($role->users()->get(), $request);
        $users = $pagination->getCollection();

        return $this->render('admin/roles/users.twig', [
            'users' => $users,
            'pagination' => $pagination,
        ]);
    }

    public function permissionsAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        if ($request->isPost()) {
            try {
                $data = $request->getParsedBody();
                if (!array_key_exists('permissions', $data) || !is_array($data['permissions'])) {
                    throw new ValidationException('Bad values');
                }
                $permissions = filter_var_array($data['permissions'], FILTER_VALIDATE_BOOLEAN, false);
                $role->permissions = $permissions;
                $role->save();
                return $this->redirect('admin_roles_list');
            } catch (Exception $e) {
                $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
                /** @var \Monolog\Logger $logger */
                $logger = $this->getContainer('log');
                $logger->error((string) $e);

                return $this->redirect('admin_roles_permissions', ['role' => $role->getRoleId()]);
            }
        }

        return $this->render('admin/roles/permissions.twig', [
            'role' => $role,
            'permissions' => self::PERMISSIONS
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return RoleInterface
     * @throws NotFoundException
     */
    protected function getRole(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $role = $this->roleRepository->findById($args['role']);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        return $role;
    }

    /**
     * @param RoleModel $role
     * @return Form
     */
    protected function getForm(RoleModel $role) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_role');
        $form
            ->add(new FormInputText('name', $role->name, [
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
            ->add(new FormInputText('slug', $role->slug, [
                'title' => 'Slug',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
			->setRules('name', ['required', 'trim', 'min_length' => 1])
			->setRules('slug', ['required', 'trim', 'min_length' => 1]);

        return $form;
    }
}
