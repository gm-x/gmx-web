<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class AdminController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveAdminMenu() {
		return 'admin_index';
	}

	public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
//		TODO: For active menu checks
//		$request->getAttribute('route')->getName();
		return $this->render('admin/index.twig');
	}
}
