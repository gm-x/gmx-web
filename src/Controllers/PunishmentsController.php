<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Punishment;

class PunishmentsController extends BaseMainController {

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
        $filter = array_key_exists('filter', $_GET) && !empty($_GET['filter']) ? $_GET['filter'] : null;
        
        if ($filter === null) {
            $players = Punishment::get();
        } else {
            $players = Punishment::where('steamid', 'LIKE', '%' . $filter . '%')
                ->orWhere('nick', 'LIKE', '%' . $filter . '%');
        }
        
		$pagination = new Pagination(Punishment::get(), $request);
		return $this->render('punishments/index.twig', [
			'punishments' => $pagination->getCollection(),
			'pagination' => $pagination,
            'filter' => $filter
		]);
    }
}
