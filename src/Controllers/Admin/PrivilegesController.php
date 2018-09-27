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
use \GameX\Core\Exceptions\PrivilegeFormException;
use \GameX\Core\Exceptions\RedirectException;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\NotAllowedException;
use \Exception;

class PrivilegesController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return PlayersConstants::ROUTE_LIST;
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
            if ($this->hasAccess($server->id, Permissions::ACCESS_LIST)) {
                $data[$server->id] = [
                    'name' => $server->name,
                    'privileges' => []
                ];
            }
        }

        /** @var Privilege $privilege */
        foreach($player->privileges()->with('group')->get() as $privilege) {
            $serverId = $privilege->group->server_id;
            if (array_key_exists($serverId, $data)) {
                $data[$serverId]['privileges'][] = $privilege;
            }
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
                return $this->redirect(PrivilegesConstants::ROUTE_EDIT, [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_privileges', 'empty_groups_list'));
            return $this->redirect(GroupsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
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
                return $this->redirect(PrivilegesConstants::ROUTE_EDIT, [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($this->getTranslate('admin_privileges', 'empty_groups_list'));
            return $this->redirect(GroupsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
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
    public function deleteAction(Request $request, Response $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
		$privilege = $this->getPrivilege($request, $response, $args, $player);
        $this->getServerForPrivilege($request, $response, $privilege, Permissions::ACCESS_DELETE);

        try {
			$privilege->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }

		return $this->redirect(PrivilegesConstants::ROUTE_LIST, ['player' => $player->id]);
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
	 * @param int $access
	 * @return Server
	 * @throws NotFoundException
	 * @throws NotAllowedException
	 */
	protected function getServerForPrivilege(Request $request, Response $response, Privilege $privilege, $access) {
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
     * @param int $serverId
     * @param int $access
     * @return bool
     */
    protected function hasAccess($serverId, $access) {
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

        if (!$permissions->hasAccessToResource(
            $user->role,
            PrivilegesConstants::PERMISSION_GROUP,
            PrivilegesConstants::PERMISSION_KEY,
            $serverId,
            $access
        )) {
            return false;
        }

        return true;
    }
}
