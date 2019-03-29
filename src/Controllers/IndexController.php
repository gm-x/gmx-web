<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Models\Server;
use \GameX\Constants\IndexConstants;
use \GameX\Constants\PreferencesConstants;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Helpers\SocialHelper;
use \GameX\Forms\SocialAuthForm;
use \GameX\Core\Jobs\JobHelper;
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
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
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
    
        $socialHelper = new SocialHelper($this->container);
        $userSocial = $socialHelper->find($provider, $profile);
        if ($userSocial && $userSocial->user) {
            $socialHelper->authenticate($userSocial);
            return $this->redirect(IndexConstants::ROUTE_INDEX);
        }

        /** @var \GameX\Core\Configuration\Config $preferences */
        $preferences = $this->getContainer('preferences');
        $autoActivate = (bool)$preferences
            ->getNode(PreferencesConstants::CATEGORY_MAIN)
            ->get(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS, false);
        $mailEnabled = (bool)$preferences->getNode('main')->get('enabled', false);

        $authHelper = new AuthHelper($this->container);
        $form = new SocialAuthForm($provider, $profile, $socialHelper, $authHelper, $autoActivate);
        if ($this->processForm($request, $form, true)) {
            $adapter->disconnect();
            if ($autoActivate) {
                $socialHelper->authenticate($form->getSocialUser());
                $this->addSuccessMessage($this->getTranslate('user', 'registered'));
            } elseif ($mailEnabled) {
                $user = $form->getUser();
                $activationCode = $authHelper->getActivationCode($user);
                JobHelper::createTask('sendmail', [
                    'type' => 'activation',
                    'user' => $user->login,
                    'email' => $user->email,
                    'title' => 'Activation',
                    'params' => [
                        'link' => $this->pathFor('activation', ['code' => $activationCode], [], true)
                    ],
                ]);
                $this->addSuccessMessage($this->getTranslate('user', 'registered_email'));
            } else {
                $this->addSuccessMessage($this->getTranslate('user', 'registered_moderate'));
            }

            return $this->redirect(IndexConstants::ROUTE_INDEX);
        }

        return $this->getView()->render($response, 'index/auth.twig', [
            'form' => $form->getForm(),
        ]);
    }
}
