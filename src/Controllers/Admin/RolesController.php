<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Core\Pagination\Pagination;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Forms\Admin\RolesForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class RolesController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return RolesConstants::ROUTE_LIST;
	}
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/roles/index.twig', [
            'roles' => RoleModel::get()
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function viewAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
        
        $pagination = new Pagination($role->users()->get(), $request);
        $users = $pagination->getCollection();
        
        return $this->render('admin/roles/view.twig', [
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
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
    
        $form = new RolesForm($role);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(RolesConstants::ROUTE_VIEW, [
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
            return $this->redirect(RolesConstants::ROUTE_VIEW, [
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
            $this->getLogger()->exception($e);
        }

        return $this->redirect(RolesConstants::ROUTE_LIST);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return RoleModel
     * @throws NotFoundException
     */
    protected function getRole(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('role', $args)) {
            return new RoleModel();
        }
        
        $role = RoleModel::find($args['role']);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        return $role;
    }
}
