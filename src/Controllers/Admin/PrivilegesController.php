<?php
namespace GameX\Controllers\Admin;

use GameX\Core\Exceptions\NotAllowedException;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Auth\Permissions;
use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PrivilegesForm;
use \GameX\Core\Exceptions\PrivilegeFormException;
use \GameX\Core\Exceptions\RedirectException;
use \Slim\Exception\NotFoundException;
use \Exception;

class PrivilegesController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_players_list';
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function indexAction(Request $request, Response $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);

        $data = [];
        /** @var Server $server */
        foreach (Server::get() as $server) {
            $data[$server->id] = [
                'name' => $server->name,
                'privileges' => []
            ];
        }

        /** @var Privilege $privilege */
        foreach($player->privileges()->with('group')->get() as $privilege) {
            $serverId = $privilege->group->server_id;
            $data[$serverId]['privileges'][] = $privilege;
        }

		return $this->render('admin/players/privileges/index.twig', [
            'player' => $player,
			'data' => $data,
		]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function createAction(Request $request, Response $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        $server = $this->getServer($request, $response, $args);
        $privilege = $this->getPrivilege($request, $response, $args, $player);
    
        $form = new PrivilegesForm($server, $privilege);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage('Add privileges groups before adding privilege');
            return $this->redirect('admin_servers_groups_create', ['server' => $server->id]);
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => true,
            'servers' => $this->getServers(),
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function editAction(Request $request, Response $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        $privilege = $this->getPrivilege($request, $response, $args, $player);
        $server = $this->getServerForPrivilege($request, $response, $privilege, Permissions::ACCESS_EDIT);
        
        $form = new PrivilegesForm($server, $privilege);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage('Add privileges groups before adding privilege');
            return $this->redirect('admin_servers_groups_create', ['server' => $server->id]);
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => false,
            'servers' => $this->getServers(),
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
		$privilege = $this->getPrivilege($request, $response, $args, $player);
        $this->getServerForPrivilege($request, $response, $privilege, Permissions::ACCESS_DELETE);

        try {
			$privilege->delete();
            $this->addSuccessMessage($this->getTranslate('admins_privileges', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }

		return $this->redirect('admin_players_privileges_list', ['player' => $player->id]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Player
	 * @throws NotFoundException
	 */
	protected function getPlayer(Request $request, Response $response, array $args) {
	    if (!array_key_exists('player', $args)) {
	        return new Player();
        }

		$player = Player::find($args['player']);
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
	protected function getServer(Request $request, Response $response, array $args) {
		$server = Server::find($args['server']);
		if (!$server) {
			throw new NotFoundException($request, $response);
		}

		return $server;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param Privilege $privilege
	 * @return Server
	 * @throws NotFoundException
	 * @throws NotAllowedException
	 */
	protected function getServerForPrivilege(Request $request, Response $response, Privilege $privilege, $access) {
	    if (!$privilege->group || !$privilege->group->server) {
            throw new NotFoundException($request, $response);
        }
        
        $server = $privilege->group->server;
        
        /** @var Permissions $permissions */
        $permissions = $this->getContainer('permissions');
        
        $user = $this->getUser();
        if (!$user) {
            throw new NotAllowedException();
        }
        
        if ($permissions->isRootUser($user)) {
            return $server;
        }
        
        if (!$user->role || !$permissions->hasAccessToResource($user->role, 'admin', 'privilege', $server->id, $access)) {
            throw new NotAllowedException();
        }

		return $server;
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param Player $player
     * @return Privilege
     * @throws NotFoundException
     */
	protected function getPrivilege(Request $request, Response $response, array $args, Player $player) {
        if (!array_key_exists('privilege', $args)) {
            return new Privilege([
                'player_id' => $player->id
            ]);
        }

        $privilege = Privilege::with('group')->find($args['privilege']);
        if (!$privilege) {
            throw new NotFoundException($request, $response);
        }

        return $privilege;
    }

	/**
	 * @return array
	 */
    protected function getServers() {
    	$servers = [];
    	/** @var Server $server */
		foreach (Server::all() as $server) {
    		$servers[$server->id] = $server->name;
		}
		return $servers;
    }
}
