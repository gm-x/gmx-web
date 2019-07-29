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
	 * @param int $playerId
	 * @param int $punishmentId
	 * @return ResponseInterface
	 * @throws NotAllowedException
	 * @throws NotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 * @throws \GameX\Core\Exceptions\RoleNotFoundException
	 */
    public function viewAction(Request $request, Response $response, $playerId, $punishmentId)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $punishment = $this->getPunishment($request, $response, $player, $punishmentId);

	    $this->getBreadcrumbs()
		    ->add(
			    $this->getTranslate('admin_menu', 'players'),
			    $this->pathFor(PlayersConstants::ROUTE_LIST)
		    )
		    ->add(
			    $player->nick,
			    $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
		    )
		    ->add($this->getTranslate('admin_punishments', 'punishments'));
        
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
     * @param int $playerId
     * @param int $serverId
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(Request $request, Response $response, $playerId, $serverId)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $server = $this->getServer($request, $response, $serverId);
        $punishment = $this->getPunishment($request, $response, $player, null, $server);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'players'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add(
                $this->getTranslate('admin_punishments', 'punishments'),
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'punishments'])
            )
            ->add($this->getTranslate('labels', 'create'));
        
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PlayersConstants::ROUTE_VIEW, [
                    'player' => $player->id,
                ], ['tab' => 'punishments']);
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
	 * @param int $playerId
	 * @param int $punishmentId
	 * @return ResponseInterface
	 * @throws NotAllowedException
	 * @throws NotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 * @throws \GameX\Core\Exceptions\RedirectException
	 * @throws \GameX\Core\Exceptions\RoleNotFoundException
	 */
    public function editAction(Request $request, Response $response, $playerId, $punishmentId)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $punishment = $this->getPunishment($request, $response, $player, $punishmentId);
        $server = $this->getServerForPunishment($punishment, Permissions::ACCESS_EDIT);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'players'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add(
                $this->getTranslate('admin_punishments', 'punishments'),
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'punishments'])
            )
            ->add($this->getTranslate('labels', 'edit'));
        
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PlayersConstants::ROUTE_VIEW, [
                    'player' => $player->id,
                ], ['tab' => 'punishments']);
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
	 * @param int $playerId
	 * @param int $punishmentId
	 * @return ResponseInterface
	 * @throws NotAllowedException
	 * @throws NotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 * @throws \GameX\Core\Exceptions\RoleNotFoundException
	 */
    public function deleteAction(Request $request, Response $response, $playerId, $punishmentId)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $punishment = $this->getPunishment($request, $response, $player, $punishmentId);
        $this->getServerForPunishment($punishment, Permissions::ACCESS_DELETE);
        
        try {
            $punishment->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }

        return $this->redirect(PlayersConstants::ROUTE_VIEW, [
            'player' => $player->id,
        ], ['tab' => 'punishments']);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return Player
     * @throws NotFoundException
     */
    protected function getPlayer(Request $request, Response $response, $id)
    {
        $player = Player::find($id);

        if (!$player) {
            throw new NotFoundException($request, $response);
        }

        return $player;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, $id)
    {
        $server = Server::find($id);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }

	/**
	 * @param Punishment $punishment
	 * @param int $access
	 * @return Server
	 * @throws NotAllowedException
	 * @throws \GameX\Core\Cache\NotFoundException
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
     * @param Player $player
     * @param int $id
     * @param Server|null $server
     * @return Punishment
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    protected function getPunishment(Request $request, Response $response, Player $player, $id = null, Server $server = null)
    {
        if ($id === null) {
            return new Punishment([
                'player_id' => $player->id,
                'punisher_id' => null,
                'punisher_user_id' => $this->getUser()->id,
                'server_id' => $server->id
            ]);
        }

        $punishment = Punishment::find($id);
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
	 * @throws \GameX\Core\Cache\NotFoundException
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
