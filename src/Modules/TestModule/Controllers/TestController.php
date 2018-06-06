<?php
namespace GameX\Modules\TestModule\Controllers;

use GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TestController extends BaseController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'test';
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
		return $this->render('modules/test/index/index.twig');
	}
}
