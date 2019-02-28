<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\AccountsConstants;
use \GameX\Core\Exceptions\RedirectException;

class AccountsController extends BaseMainController
{
    protected function getActiveMenu()
    {
        return AccountsConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function indexAction(Request $request, ResponseInterface $response, array $args)
    {
        $user = $this->getUser();
        return $this->render('accounts/index.twig', [
            'user' => $user
        ]);
    }
}
