<?php

namespace GameX\Controllers\Admin;

use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Constants\Admin\GroupsConstants;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Auth\Permissions;
use \GameX\Models\Player;
use \GameX\Models\Server;
use \GameX\Models\Privilege;
use \GameX\Forms\Admin\PrivilegesForm;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;
use \GameX\Core\Exceptions\PrivilegeFormException;
use \GameX\Core\Exceptions\RedirectException;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\NotAllowedException;
use \Exception;

class PrivilegesController extends BaseAdminController
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
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function indexAction(Request $request, Response $response, $playerId)
    {
        $player = $this->getPlayer($request, $response, $playerId);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'users'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add($this->getTranslate('admin_privileges', 'privileges'));

        $data = [];
        /** @var Server $server */
        foreach (Server::get() as $server) {
            if ($this->hasAccess($server->id, Permissions::ACCESS_LIST)) {
                $data[$server->id] = [
                    'name' => $server->name,
                    'privileges' => []
                ];
            }
        }
        
        /** @var Privilege $privilege */
        foreach ($player->privileges()->with('group')->get() as $privilege) {
            $serverId = $privilege->group->server_id;
            if (array_key_exists($serverId, $data)) {
                $data[$serverId]['privileges'][] = $privilege;
            }
        }
        
        return $this->getView()->render($response, 'admin/players/privileges/index.twig', [
            'player' => $player,
            'data' => $data,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $playerId
     * @param int $serverId
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function createAction(Request $request, Response $response, $playerId, $serverId)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $server = $this->getServer($request, $response, $serverId);
        $privilege = $this->getPrivilege($request, $response, null, $player);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'users'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add(
                $this->getTranslate('admin_privileges', 'privileges'),
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'privileges'])
            )
            ->add($this->getTranslate('labels', 'create'));

        $form = new PrivilegesForm($server, $privilege);
        try {
            if ($this->processForm($request, $form)) {
	            $this->reloadAdmins($server);
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'privileges']);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_privileges', 'empty_groups_list'));
            return $this->redirect(GroupsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->getView()->render($response, 'admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $playerId
     * @param int $id
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function editAction(Request $request, Response $response, $playerId, $id)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $privilege = $this->getPrivilege($request, $response, $id);
        $server = $this->getServerForPrivilege($request, $response, $privilege, Permissions::ACCESS_EDIT);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'users'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add(
                $this->getTranslate('admin_privileges', 'privileges'),
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'privileges'])
            )
            ->add($this->getTranslate('labels', 'edit'));

        $form = new PrivilegesForm($server, $privilege);
        try {
            if ($this->processForm($request, $form)) {
	            $this->reloadAdmins($server);
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'privileges']);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_privileges', 'empty_groups_list'));
            return $this->redirect(GroupsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->getView()->render($response, 'admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $playerId
     * @param int $id
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, $playerId, $id)
    {
        $player = $this->getPlayer($request, $response, $playerId);
        $privilege = $this->getPrivilege($request, $response, $id);
        $server = $this->getServerForPrivilege($request, $response, $privilege, Permissions::ACCESS_DELETE);
        
        try {
            $privilege->delete();
	        $this->reloadAdmins($server);
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(PlayersConstants::ROUTE_VIEW, ['player' => $player->id], ['tab' => 'privileges']);
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
     * @param int $serverId
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, $serverId)
    {
        $server = Server::find($serverId);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param Privilege $privilege
     * @param int $access
     * @return Server
     * @throws NotFoundException
     * @throws NotAllowedException
     */
    protected function getServerForPrivilege(Request $request, Response $response, Privilege $privilege, $access)
    {
        if (!$privilege->group || !$privilege->group->server) {
            throw new NotFoundException($request, $response);
        }
        
        $server = $privilege->group->server;
        
        if (!$this->hasAccess($server->id, $access)) {
            throw new NotAllowedException();
        }
        
        return $server;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @param Player $player
     * @return Privilege
     * @throws NotFoundException
     */
    protected function getPrivilege(Request $request, Response $response, $id = null, Player $player = null)
    {
        if ($id === null) {
            return new Privilege([
                'player_id' => $player->id
            ]);
        }
        
        $privilege = Privilege::with('group')->find($id);
        if (!$privilege) {
            throw new NotFoundException($request, $response);
        }
        
        return $privilege;
    }
    
    protected function hasAccess($serverId, $access)
    {
        return $this->getPermissions()->hasUserAccessToResource(PrivilegesConstants::PERMISSION_GROUP,
            PrivilegesConstants::PERMISSION_KEY, $serverId, $access);
    }

	/**
	 * @param Server $server
	 */
	protected function reloadAdmins(Server $server)
	{
		JobHelper::createTaskIfNotExists('rcon_exec', [
			'server_id' => $server->id,
			'command' => 'amx_reloadadmins'
		], null, function (Task $task) use ($server) {
			return isset($task->data['server_id']) && $task->data['server_id'] == $server->id;
		});
	}
}
