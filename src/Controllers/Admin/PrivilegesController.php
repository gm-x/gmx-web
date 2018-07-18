<?php
namespace GameX\Controllers\Admin;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\BaseAdminController;
use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Server;
use \GameX\Models\Group;
use \GameX\Core\Pagination\Pagination;
use \GameX\Core\Forms\Form;
use GameX\Core\Forms\Elements\Checkbox;
use GameX\Core\Forms\Elements\Date;
use GameX\Core\Forms\Elements\Text;
use GameX\Core\Forms\Elements\Select;
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
        $form = $this
            ->getForm($privilege)
            ->setAction($request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $privilege->player_id = $player->id;
					$privilege->group_id = $form->get('group')->getValue();
					$privilege->prefix = $form->get('prefix')->getValue();
					$privilege->expired_at = $form->get('expired')->getDate();
					$privilege->active = $form->get('active')->getValue() ? 1 : 0;
                    $privilege->save();
                    return $this->redirect('admin_players_privileges_list', ['player' => $player->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form,
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
        $form = $this
            ->getForm($privilege)
            ->setAction($request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $privilege->group_id = $form->get('group')->getValue();
                    $privilege->prefix = $form->get('prefix')->getValue();
                    $privilege->expired_at = $form->get('expired')->getDate();
                    $privilege->active = $form->get('active')->getValue() ? 1 : 0;
                    $privilege->save();
                    return $this->redirect('admin_players_privileges_list', ['player' => $player->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form,
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
        } catch (Exception $e) {
            $this->addErrorMessage('Something wrong. Please Try again later.');
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
     * @param Privilege $privilege
     * @return Form
     */
    protected function getForm(Privilege $privilege) {
    	$server = $privilege->exists
			? $privilege->group->server
			: Server::first();

    	$servers = $this->getServers();
    	$groups = $this->getGroups($server);

		$exists = function ($group) {
			return Group::where('id', '=', $group)->exists();
		};

		return $this->createForm('admin_server_group')
            ->add(new Select('server', $server->id, $servers, [
                'id' => 'input_admin_server',
                'title' => 'Server',
                'error' => 'Required',
                'required' => true,
				'empty_option' => 'Choose server',
            ]))
			->add(new Select('group', $privilege->group_id, $groups, [
                'id' => 'input_player_group',
                'title' => 'Group',
                'error' => 'Required',
                'required' => true,
				'empty_option' => 'Choose group',
            ]))
            ->add(new Text('prefix', $privilege->prefix, [
                'title' => 'Prefix',
                'error' => '',
                'required' => false,
            ]))
            ->add(new Date('expired', $privilege->expired_at, [
                'title' => 'Expired',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new Checkbox('active', $privilege->active ? true : false, [
                'title' => 'Active',
                'required' => false,
            ]))
			->setRules('server', ['required', 'integer', 'in' => array_keys($servers)])
			->setRules('group', ['required', 'integer', 'exists' => $exists])
			->setRules('prefix', ['trim'])
			->setRules('expired', ['required', 'date'])
			->setRules('active', ['bool']);
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
