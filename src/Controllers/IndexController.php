<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Models\Server;
use \GameX\Constants\IndexConstants;
use \GameX\Core\Auth\Helpers\SocialHelper;
use \GameX\Forms\User\SocialAuthForm;
use \Slim\Exception\NotFoundException;

class IndexController extends BaseMainController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return 'index';
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function indexAction(Request $request, Response $response, array $args)
    {
        $servers = Server::with('map')->where('active', true)->get();

        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');

        $players = [];
        foreach (Server::all() as $server) {
            $players[$server->id] = $cache->get('players_online', $server);
        }

        return $this->getView()->render($response, 'index/index.twig', [
            'servers' => $servers,
            'players' => $players,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws \GameX\Core\Lang\Exceptions\BadLanguageException
     */
    public function languageAction(Request $request, Response $response, array $args)
    {
        /** @var Language $lang */
        $lang = $this->getContainer('lang');
        $lang->setUserLang($request->getParsedBodyParam('lang'));
        return $response->withJson(['success', true]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function authAction(Request $request, Response $response, array $args)
    {
        /** @var \GameX\Core\Auth\Social\SocialAuth $social */
        $social = $this->getContainer('social');
    
        $provider = $args['provider'];
        
        if (!$social->hasProvider($provider)) {
            throw new NotFoundException($request, $response);
        }
        
        $adapter = $social->getProvider($provider);

        $adapter->authenticate();

        if ($social->isRedirected()) {
        	return $this->redirectTo($social->getRedirectUrl());
        }
    
        $profile = $adapter->getUserProfile();
        $adapter->disconnect();
    
        $helper = new SocialHelper($this->container);
        $userSocial = $helper->find($provider, $profile);
        if ($userSocial && $userSocial->user) {
            $helper->authenticate($userSocial);
            return $this->redirect(IndexConstants::ROUTE_INDEX);
        }

        $form = new SocialAuthForm($helper, true);
        if ($this->processForm($request, $form, true)) {
            return $this->redirect(IndexConstants::ROUTE_INDEX);
        }

//        $userSocial = $helper->register($provider, $profile);
        
//        if (!$helper->authenticate($userSocial)) {
//            $this->addErrorMessage('Some errors');
//            return $this->redirect('login');
//        } else {
//            return $this->redirect('index');
//        }

        return $this->getView()->render($response, 'index/auth.twig', [
            'form' => $form->getForm(),
        ]);
    }
}
