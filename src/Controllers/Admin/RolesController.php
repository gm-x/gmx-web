<?php
namespace GameX\Controllers\Admin;

use \Cartalyst\Sentinel\Roles\RoleInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\BaseController;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \Slim\Exception\NotFoundException;
use \Exception;

class RolesController extends BaseController {
    const PERMISSIONS = [
        'index' => 'Index',
        'admin.users' => 'Admin Users CRUD',
        'admin.roles' => 'Admin Roles CRUD',
        'admin.servers' => 'Admin Servers CRUD',
        'admin.user.role' => 'Admin User Set Role',
        'admin.players' => 'Admin Players Role',
		'admin.servers.groups' => 'Admin Privileges Groups CRUD',
    ];

    /** @var  RoleRepositoryInterface */
    protected $roleRepository;

    public function init() {
        $this->roleRepository = $this->getContainer('auth')->getRoleRepository();
    }

    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/roles/index.twig', [
            'roles' => $this->roleRepository->get()
        ]);
    }

    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_roles_create');
        $form
            ->setAction((string)$request->getUri())
            ->add('name', '', [
                'type' => 'text',
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim'])
            ->add('slug', '', [
                'type' => 'text',
                'title' => 'Slug',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim'])
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
        ]);
    }

    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_roles_edit');
        $form
            ->setAction((string)$request->getUri())
            ->add('name', $role->name, [
                'type' => 'text',
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim'])
            ->add('slug', $role->slug, [
                'type' => 'text',
                'title' => 'Slug',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim'])
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $role->name = $form->getValue('name');
                    $role->slug = $form->getValue('slug');
                    $role->save();
                    return $this->redirect('admin_roles_list');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/roles/form.twig', [
            'form' => $form,
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
}
