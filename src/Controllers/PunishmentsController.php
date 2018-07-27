<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\ServerRequestInterface;
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
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $filter = array_key_exists('filter', $_GET) && !empty($_GET['filter']) ? $_GET['filter'] : null;
        
        if ($filter === null) {
            $punishments = Punishment::get();
        } else {
            $punishments = Punishment::where('steamid', 'LIKE', '%' . $filter . '%')
                ->orWhere('nick', 'LIKE', '%' . $filter . '%')
                ->get();
        }
    
        $pagination = new Pagination($punishments, $request);
        return $this->render('punishments/index.twig', [
			'punishments' => $pagination->getCollection(),
			'pagination' => $pagination,
            'filter' => $filter
		]);
    }
}
