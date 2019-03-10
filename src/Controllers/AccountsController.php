<?php

namespace GameX\Controllers;

use GameX\Core\Auth\Models\UserModel;
use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\AccountsConstants;
use \GameX\Models\Player;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\NotAllowedException;

class AccountsController extends BaseMainController
{
    protected function getActiveMenu()
    {
        return AccountsConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(Request $request, Response $response, array $args)
    {
        $user = $this->getUser();
        return $this->getView()->render($response, 'accounts/index.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    public function viewAction(Request $request, Response $response, array $args)
    {
        $user = $this->getUser();
        $player = $this->getPlayer($request, $response, $args, $user);
        return $this->getView()->render($response, 'accounts/view.twig', [
            'user' => $user,
            'player' => $player
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    public function editAction(Request $request, Response $response, array $args)
    {
//        $user = $this->getUser();
//        $player = $this->getPlayer($request, $response, $args, $user);
//        $player->setAttribute($request->getParsedBodyParam('key'), $request->getParsedBodyParam('value'));
//        $player->save();
        return $response->withJson([
            'success' => true
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param UserModel $user
     * @return Player
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    protected function getPlayer(Request $request, Response $response, array $args, UserModel $user)
    {
        if (!array_key_exists('player', $args)) {
            throw new NotFoundException($request, $response);
        }
        
        $player = Player::find($args['player']);
        if (!$player) {
            throw new NotFoundException($request, $response);
        }
        
        if ($player->user_id !== $user->id) {
            throw new NotAllowedException();
        }
    
        return $player;
    }
}