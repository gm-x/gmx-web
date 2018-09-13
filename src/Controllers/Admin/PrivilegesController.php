<?php
namespace GameX\Controllers\Admin;

use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\BaseAdminController;
use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PrivilegesForm;
use \GameX\Core\Pagination\Pagination;
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
		$pagination = new Pagination($player->privileges()->get(), $request);
		return $this->render('admin/players/privileges/index.twig', [
            'player' => $player,
			'privileges' => $pagination->getCollection(),
			'pagination' => $pagination,
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
        $privilege = $this->getPrivilege($request, $response, $args);
        $privilege->player_id = $player->id;
    
        $form = new PrivilegesForm($privilege);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($e->getMessage());
            return $this->redirect($e->getPath(), $e->getParams());
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
        $privilege = $this->getPrivilege($request, $response, $args);
        
        $form = new PrivilegesForm($privilege);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (PrivilegeFormException $e) {
            $this->addErrorMessage($e->getMessage());
            return $this->redirect($e->getPath(), $e->getParams());
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
		$privilege = $this->getPrivilege($request, $response, $args);

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
	 * @return ResponseInterface
	 * @throws NotFoundException
	 */
    public function groupsAction(Request $request, Response $response, array $args = []) {
        if (!array_key_exists('server', $_GET)) {
            throw new NotFoundException($request, $response);
        }
        $server = Server::find($_GET['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }

        return $response->withJson([
            'groups' => $this->getGroups($server),
        ]);
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
     * @return Privilege
     * @throws NotFoundException
     */
	protected function getPrivilege(Request $request, Response $response, array $args) {
        if (!array_key_exists('privilege', $args)) {
            return new Privilege();
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

	/**
	 * @param Server $server
	 * @return array
	 */
    protected function getGroups(Server $server) {
    	$groups = [];
    	foreach ($server->groups as $group) {
    		$groups[$group->id] = $group->title;
		}
		return $groups;
	}
}
