<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Lang\Language;
use \GameX\Models\Server;

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
    
    public function testAction(Request $request, Response $response, array $args)
    {
        $config = [
            //Location where to redirect users once they authenticate with Facebook
            //For this example we choose to come back to this same script
            'callback' => 'https://gm-x.info/demo/test',
            'openid_identifier' => 'http://steamcommunity.com/openid'
        ];
    
        try {
            //Instantiate Facebook's adapter directly
            $adapter = new \Hybridauth\Provider\Steam($config);
    
            $adapter->authenticate();
//            $tokens = $adapter->getAccessToken();
            $userProfile = $adapter->getUserProfile();
    
//            $social = \GameX\Core\Auth\Models\UserSocialModel::find();
    
            /** @var \Cartalyst\Sentinel\Sentinel $auth */
            $auth = $this->getContainer('auth');
            $user = $auth->getUserRepository()->create([
                'login' => $userProfile->displayName,
                'email' => null,
                'token' => \GameX\Core\Utils::generateToken(16),
                'is_social' => 1
            ]);
            $auth->activate($user);
            
            $social = new \GameX\Core\Auth\Models\UserSocialModel();
            $social->fill([
                'user_id' => $user->id,
                'identifier' => $userProfile->identifier,
                'photo_url' => $userProfile->photoURL,
            ]);
            $social->save();
            $adapter->disconnect();
            return $this->redirect('index');
        }
        catch(\Exception $e ){
            echo $e->getMessage();
        }
        die();
    }
}
