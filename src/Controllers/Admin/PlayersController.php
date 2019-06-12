<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Permissions;
use \GameX\Forms\Admin\PlayersForm;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Player;
use \GameX\Models\Server;
use \GameX\Models\Privilege;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Constants\Admin\PunishmentsConstants;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;
use \Slim\Exception\NotFoundException;
use \Exception;

class PlayersController extends BaseAdminController
{
    
    protected function getActiveMenu()
    {
        return PlayersConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function indexAction(Request $request, Response $response)
    {
        $filter = array_key_exists('filter', $_GET) && !empty($_GET['filter']) ? $_GET['filter'] : null;
        $players = $filter === null ? Player::get() : Player::filterCollection($filter)->get();

        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_menu', 'players'));
        
        $pagination = new Pagination($players, $request);
        return $this->getView()->render($response, 'admin/players/index.twig', [
            'players' => $pagination->getCollection(),
            'pagination' => $pagination,
            'filter' => $filter
        ]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param $id
	 * @return ResponseInterface
	 * @throws NotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 * @throws \GameX\Core\Exceptions\RoleNotFoundException
	 */
    public function viewAction(Request $request, Response $response, $id)
    {
        $player = $this->getPlayer($request, $response, $id);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'players'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add($player->nick);
        
        $privileges = [];
        $servers = [];
        /** @var Server $server */
        foreach (Server::get() as $server) {
            if ($this->getPermissions()->hasUserAccessToResource(PunishmentsConstants::PERMISSION_GROUP,
                PunishmentsConstants::PERMISSION_KEY, $server->id, Permissions::ACCESS_CREATE)) {
                $servers[$server->id] = $server->name;
            }
            
            if ($this->getPermissions()->hasUserAccessToResource(PrivilegesConstants::PERMISSION_GROUP,
                PrivilegesConstants::PERMISSION_KEY, $server->id, Permissions::ACCESS_LIST)) {
                $privileges[$server->id] = [
                    'name' => $server->name,
                    'privileges' => []
                ];
            }
        }
        
        /** @var Privilege $privilege */
        foreach ($player->privileges()->with('group')->get() as $privilege) {
            $serverId = $privilege->group->server_id;
            if (array_key_exists($serverId, $privileges)) {
                $privileges[$serverId]['privileges'][] = $privilege;
            }
        }
        
        return $this->getView()->render($response, 'admin/players/view.twig', [
            'player' => $player,
            'privileges' => $privileges,
            'servers' => $servers,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(Request $request, Response $response)
    {
        $player = $this->getPlayer($request, $response);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'players'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add($this->getTranslate('labels', 'create'));

        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PlayersConstants::ROUTE_EDIT, [
                'player' => $player->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/players/form.twig', [
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(Request $request, Response $response, $id)
    {
        $player = $this->getPlayer($request, $response, $id);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'players'),
                $this->pathFor(PlayersConstants::ROUTE_LIST)
            )
            ->add(
                $player->nick,
                $this->pathFor(PlayersConstants::ROUTE_VIEW, ['player' => $player->id])
            )
            ->add($this->getTranslate('labels', 'edit'));

        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PlayersConstants::ROUTE_EDIT, [
                'player' => $player->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/players/form.twig', [
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, $id)
    {
        $player = $this->getPlayer($request, $response, $id);
        
        try {
            $player->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(PlayersConstants::ROUTE_LIST);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param int $id
     * @return Player
     * @throws NotFoundException
     */
    protected function getPlayer(Request $request, Response $response, $id = null)
    {
        if ($id === null) {
            return new Player();
        }
        
        $player = Player::find($id);
        if (!$player) {
            throw new NotFoundException($request, $response);
        }
        
        return $player;
    }

	/**
	 * @param Server $server
	 * @param Player $player
	 */
	protected function reloadAdmins(Server $server, Player $player)
	{
		JobHelper::createTaskIfNotExists('rcon_exec', [
			'server_id' => $server->id,
			'command' => 'amx_reloadadmins'
		], null, function (Task $task) use ($server) {
			return isset($task->data['server_id']) && $task->data['server_id'] == $server->id;
		});
	}
}
