<?php
namespace GameX\Controllers\Admin;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\BaseAdminController;
use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PrivilegesForm;
use \GameX\Core\Pagination\Pagination;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;
use \Exception;

class PrivilegesController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_players_list';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
		$pagination = new Pagination($player->privileges()->get(), $request);
		return $this->render('admin/players/privileges/index.twig', [
            'player' => $player,
			'privileges' => $pagination->getCollection(),
			'pagination' => $pagination,
		]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        $privilege = $this->getPrivilege($request, $response, $args);
        $privilege->player_id = $player->id;
    
        $form = new PrivilegesForm($privilege);
        try {
            $form->create();
        
            if ($form->process($request)) {
                $this->addSuccessMessage($this->getTranslate('admins_privileges', 'created'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (FormException $e) {
            $form->getForm()->setError($e->getField(), $e->getMessage());
            return $this->redirectTo($form->getForm()->getAction());
        } catch (ValidationException $e) {
            if ($e->hasMessage()) {
                $this->addErrorMessage($e->getMessage());
            }
            return $this->redirectTo($form->getForm()->getAction());
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => true,
            'servers' => $this->getServers(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        $privilege = $this->getPrivilege($request, $response, $args);
        
        $form = new PrivilegesForm($privilege);
        try {
            $form->create();
        
            if ($form->process($request)) {
                $this->addSuccessMessage($this->getTranslate('admins_privileges', 'updated'));
                return $this->redirect('admin_players_privileges_edit', [
                    'player' => $player->id,
                    'privilege' => $privilege->id,
                ]);
            }
        } catch (FormException $e) {
            $form->getForm()->setError($e->getField(), $e->getMessage());
            return $this->redirectTo($form->getForm()->getAction());
        } catch (ValidationException $e) {
            if ($e->hasMessage()) {
                $this->addErrorMessage($e->getMessage());
            }
            return $this->redirectTo($form->getForm()->getAction());
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => false,
            'servers' => $this->getServers(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
		$privilege = $this->getPrivilege($request, $response, $args);

        try {
			$privilege->delete();
            $this->addSuccessMessage($this->getTranslate('admins_privileges', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }

		return $this->redirect('admin_players_privileges_list', ['player' => $player->id]);
    }

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return \Slim\Http\Response
	 * @throws NotFoundException
	 */
    public function groupsAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
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
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return Player
	 * @throws NotFoundException
	 */
	protected function getPlayer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Privilege
     * @throws NotFoundException
     */
	protected function getPrivilege(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
