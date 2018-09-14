<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \GameX\Forms\Admin\PermissionsForm;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PermissionsConstants;
use \Slim\Exception\NotFoundException;

class PermissionsController extends BaseAdminController {

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
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
        $form = new PermissionsForm($role);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PermissionsConstants::ROUTE_LIST, [
                'role' => $role->id,
            ]);
        }
        
        return $this->render('admin/roles/permissions/index.twig', [
            'form' => $form->getForm(),
            'list' => $form->getList(),
            'servers' => $form->getServers()
        ]);
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
            throw new NotFoundException($request, $response);
        }
    
        $role = RoleModel::find($args['role']);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }
        
        return $role;
    }
}
