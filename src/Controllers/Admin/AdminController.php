<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class AdminController extends BaseController {
	public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
//		TODO: For active menu checks
//		$request->getAttribute('route')->getName();
		return $this->render('admin/index.twig');
	}
}
