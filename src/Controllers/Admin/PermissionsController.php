<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class PermissionsController extends BaseAdminController {
    
    /**
     * @return string
     */
    protected function getActiveMenu() {
        return 'admin_roles_list';
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        /** @var \GameX\Core\Auth\Permissions\Manager $manager */
        $manager = $this->getContainer('permissions');
        $permissions = $manager->getPermissionsList();
        
        return $this->render('admin/index.twig');
    }
}
