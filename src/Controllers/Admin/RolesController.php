<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Core\Auth\Models\RoleModel;
use \Cartalyst\Sentinel\Roles\RoleInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use \GameX\Forms\Admin\RolesForm;
use \GameX\Core\Exceptions\ValidationException;
use \Slim\Exception\NotFoundException;
use \Exception;

class RolesController extends BaseAdminController {
    const PERMISSIONS = [
        'index' => 'Index',
        'admin.preferences' => 'Site Preferences',
        'admin.users' => 'Admin Users CRUD',
        'admin.roles' => 'Admin Roles CRUD',
        'admin.servers' => 'Admin Servers CRUD',
        'admin.user.role' => 'Admin User Set Role',
        'admin.players' => 'Admin Players Role',
		'admin.servers.groups' => 'Admin Privileges Groups CRUD',
		'admin.servers.reasons' => 'Admin Reasons Groups CRUD',
		'admin.players.privileges' => 'Admin Players Privileges CRUD',
    ];

    /** @var  RoleRepositoryInterface */
    protected $roleRepository;

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_roles_list';
	}
    
    /**
     * Init RolesController
     */
	public function init() {
        $this->roleRepository = $this->getContainer('auth')->getRoleRepository();
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/roles/index.twig', [
            'roles' => $this->roleRepository->get()
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
    
        $form = new RolesForm($role);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_roles_edit', [
                'role' => $role->id,
            ]);
        }

        return $this->render('admin/roles/form.twig', [
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
        $form = new RolesForm($role);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_roles_edit', [
                'role' => $role->id,
            ]);
        }

        return $this->render('admin/roles/form.twig', [
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        try {
            if ($role->users()->count() > 0) {
                $this->addErrorMessage('There users attached to role');
            } else {
                $role->delete();
            }
        } catch (Exception $e) {
            $this->addErrorMessage('Something wrong. Please Try again later.');
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }

        return $this->redirect('admin_roles_list');
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);

        $pagination = new Pagination($role->users()->get(), $request);
        $users = $pagination->getCollection();

        return $this->render('admin/roles/users.twig', [
            'users' => $users,
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
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
                $this->addErrorMessage('Something wrong. Please Try again later.');
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
        if (!array_key_exists('role', $args)) {
            return new RoleModel();
        }
        
        $role = $this->roleRepository->findById($args['role']);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        return $role;
    }
}
