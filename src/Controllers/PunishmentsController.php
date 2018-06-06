<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Punishment;

class PunishmentsController extends BaseController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'punishments';
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
		$pagination = new Pagination(Punishment::get(), $request);
		return $this->render('punishments/index.twig', [
			'punishments' => $pagination->getCollection(),
			'pagination' => $pagination,
		]);
    }
}
