<?php
namespace GameX\Modules\TestModule\Controllers;

use GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TestController extends BaseController {
	public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
		return $this->render('modules/test/index/index.twig');
	}
}
