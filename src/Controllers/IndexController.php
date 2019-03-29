<?php

namespace GameX\Controllers;

use GameX\Core\Auth\Helpers\SocialHelper;
use \GameX\Core\BaseMainController;
use Slim\Exception\NotFoundException;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Models\Server;
use \Hybridauth\HttpClient\Util as HybridauthUtil;
use \GameX\Core\Auth\Social\RedirectHelper;

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
    
    public function authAction(Request $request, Response $response, array $args)
    {
        /** @var \GameX\Core\Auth\Social\SocialAuth $social */
        $social = $this->getContainer('social');
    
        $provider = $args['provider'];
        
        if (!$social->hasProvider($provider)) {
            throw new NotFoundException($request, $response);
        }
        
        $adapter = $social->getProvider($provider);

        $redirectUrl = null;
	    HybridauthUtil::setRedirectHandler([RedirectHelper::class, 'redirect']);
        $adapter->authenticate();

        if (RedirectHelper::isRedirected()) {
        	return $this->redirectTo(RedirectHelper::getUrl());
        }
    
        $profile = $adapter->getUserProfile();
        $adapter->disconnect();
    
        $helper = new SocialHelper($this->container);
        $userSocial = $helper->find($provider, $profile);
        if (!$userSocial) {
            $userSocial = $helper->register($provider, $profile);
        }
        
        if (!$helper->authenticate($userSocial)) {
            $this->addErrorMessage('Some errors');
            return $this->redirect('login');
        } else {
            return $this->redirect('index');
        }
    }
}
