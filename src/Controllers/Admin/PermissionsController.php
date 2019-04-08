<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Cache\Cache;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Forms\Admin\PermissionsForm;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PermissionsConstants;
use \Slim\Exception\NotFoundException;

class PermissionsController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return RolesConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $roleId
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(Request $request, Response $response, $roleId)
    {
        $role = $this->getRole($request, $response, $roleId);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'roles'),
                $this->pathFor(RolesConstants::ROUTE_LIST)
            )
            ->add(
                $role->name,
                $this->pathFor(RolesConstants::ROUTE_VIEW, ['role' => $role->id])
            )
            ->add($this->getTranslate('admin_menu', 'permissions'));

        $form = new PermissionsForm($role);
        if ($this->processForm($request, $form)) {
            /** @var Cache $cache */
            $cache = $this->getContainer('cache');
            $cache->clear('permissions');
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PermissionsConstants::ROUTE_LIST, [
                'role' => $role->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/roles/permissions/index.twig', [
            'form' => $form->getForm(),
            'list' => $form->getList(),
            'servers' => $form->getServers()
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return RoleModel
     * @throws NotFoundException
     */
    protected function getRole(Request $request, Response $response, $id)
    {
        $role = RoleModel::find($id);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }
        
        return $role;
    }
}
