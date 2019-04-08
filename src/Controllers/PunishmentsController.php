<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Punishment;

class PunishmentsController extends BaseMainController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return 'punishments';
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(Request $request, ResponseInterface $response)
    {
        $filter = array_key_exists('filter', $_GET) && !empty($_GET['filter']) ? $_GET['filter'] : null;
        
        if ($filter === null) {
            $punishments = Punishment::with('server', 'reason')->get();
        } else {
            $punishments = Punishment::with('server', 'reason')->where('steamid', 'LIKE',
                    '%' . $filter . '%')->orWhere('nick', 'LIKE', '%' . $filter . '%')->get();
        }
        
        $pagination = new Pagination($punishments, $request);
        return $this->getView()->render($response, 'punishments/index.twig', [
            'punishments' => $pagination->getCollection(),
            'pagination' => $pagination,
            'filter' => $filter
        ]);
    }
}
