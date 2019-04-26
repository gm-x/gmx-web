<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Models\Server;
use \GameX\Constants\IndexConstants;

class IndexController extends BaseMainController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return IndexConstants::ROUTE_INDEX;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function indexAction(Request $request, Response $response)
    {
        $servers = Server::with('map')->where('active', true)->get();

        return $this->getView()->render($response, 'index/index.twig', [
            'servers' => $servers,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws \GameX\Core\Lang\Exceptions\BadLanguageException
     */
    public function languageAction(Request $request, Response $response)
    {
        /** @var Language $lang */
        $lang = $this->getContainer('lang');
        $lang->setUserLang($request->getParsedBodyParam('lang'));
        return $response->withJson(['success', true]);
    }
}
