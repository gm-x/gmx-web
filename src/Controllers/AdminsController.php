<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \Carbon\Carbon;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Group;
use \GameX\Models\Privilege;

class AdminsController extends BaseMainController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return 'admins';
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(Request $request, ResponseInterface $response)
    {
        $groups = Group::get()->pluck('id')->all();

        $privileges = Privilege::where('active', 1)
            ->whereIn('group_id', $groups)
            ->where(function ($query) {
                $query
                    ->whereNull('expired_at')
                    ->orWhere('expired_at','>=', Carbon::today()->toDateString());
            })
            ->get();

        $pagination = new Pagination($privileges, $request);
        return $this->getView()->render($response, 'admins/index.twig', [
            'privileges' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
}
