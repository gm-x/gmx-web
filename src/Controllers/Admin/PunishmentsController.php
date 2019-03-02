<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\Auth\Permissions;
use \GameX\Models\Punishment;
use \GameX\Models\Player;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PunishmentsForm;
use \GameX\Constants\Admin\PunishmentsConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Core\Exceptions\PunishmentsFormException;
use \GameX\Core\Exceptions\NotAllowedException;
use \Slim\Exception\NotFoundException;
use \Exception;

class PunishmentsController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return PlayersConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function viewAction(Request $request, Response $response, array $args = [])
    {
        $player = $this->getPlayer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player);
        
        $hasAccess = $this->getPermissions()->hasUserAccessToResource(PunishmentsConstants::PERMISSION_GROUP,
            PunishmentsConstants::PERMISSION_KEY, $punishment->server_id, Permissions::ACCESS_VIEW);
        
        if (!$hasAccess) {
            throw new NotAllowedException();
        }
        
        return $this->getView()->render($response, 'admin/players/punishments/view.twig', [
            'player' => $player,
            'punishment' => $punishment,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(Request $request, Response $response, array $args = [])
    {
        $player = $this->getPlayer($request, $response, $args);
        $server = $this->getServer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player, $server);
        
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PunishmentsConstants::ROUTE_VIEW, [
                    'player' => $player->id,
                    'punishment' => $punishment->id,
                ]);
            }
        } catch (PunishmentsFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_punishments', 'empty_reasons_list'));
            return $this->redirect(ReasonsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->getView()->render($response, 'admin/players/punishments/form.twig', [
            'player' => $player,
            'punishment' => $punishment,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(Request $request, Response $response, array $args = [])
    {
        $player = $this->getPlayer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player);
        $server = $this->getServerForPunishment($punishment, Permissions::ACCESS_DELETE);
        
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PunishmentsConstants::ROUTE_VIEW, [
                    'player' => $player->id,
                    'punishment' => $punishment->id,
                ]);
            }
        } catch (PunishmentsFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_punishments', 'empty_reasons_list'));
            return $this->redirect(ReasonsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->getView()->render($response, 'admin/players/punishments/form.twig', [
            'player' => $player,
            'punishment' => $punishment,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, array $args = [])
    {
        $player = $this->getPlayer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player);
        $this->getServerForPunishment($punishment, Permissions::ACCESS_DELETE);
        
        try {
            $punishment->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(PunishmentsConstants::ROUTE_LIST, ['player' => $player->id]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param bool $withPunishments
     * @return Player
     * @throws NotFoundException
     */
    protected function getPlayer(Request $request, Response $response, array $args, $withPunishments = false)
    {
        if (!array_key_exists('player', $args)) {
            return new Player();
        }
        
        $player = $withPunishments ? Player::with('punishments')->find($args['player']) : Player::find($args['player']);
        
        if (!$player) {
            throw new NotFoundException($request, $response);
        }
        
        return $player;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, array $args)
    {
        $server = Server::find($args['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param Punishment $punishment
     * @param $access
     * @return Server
     * @throws NotAllowedException
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    protected function getServerForPunishment(Punishment $punishment, $access)
    {
        if (!$this->hasAccess($punishment->server_id, $access)) {
            throw new NotAllowedException();
        }
        
        return $punishment->server;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param Player $player
     * @param Server|null $server
     * @return Punishment
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    protected function getPunishment(
        Request $request,
        Response $response,
        array $args,
        Player $player,
        Server $server = null
    ) {
        if (!array_key_exists('punishment', $args)) {
            return new Punishment([
                'player_id' => $player->id,
                'punisher_id' => null,
                'punisher_user_id' => $this->getUser()->id,
                'server_id' => $server->id
            ]);
        }
        
        $punishment = Punishment::find($args['punishment']);
        if (!$punishment) {
            throw new NotFoundException($request, $response);
        }
        
        if ($punishment->player_id !== $player->id) {
            throw new NotAllowedException();
        }
        
        return $punishment;
    }
    
    /**
     * @param $serverId
     * @param $access
     * @return bool
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    protected function hasAccess($serverId, $access)
    {
        /** @var Permissions $permissions */
        $permissions = $this->getContainer('permissions');
        
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        if ($permissions->isRootUser($user)) {
            return true;
        }
        
        if (!$user->role) {
            return false;
        }
        
        if (!$permissions->hasAccessToResource($user->role, PunishmentsConstants::PERMISSION_GROUP,
            PunishmentsConstants::PERMISSION_KEY, $serverId, $access)) {
            return false;
        }
        
        return true;
    }
}
