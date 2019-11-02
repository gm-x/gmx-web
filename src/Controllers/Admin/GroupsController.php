<?php

namespace GameX\Controllers\Admin;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Response;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\BaseAdminController;
use \GameX\Models\Server;
use \GameX\Models\Group;
use \GameX\Forms\Admin\GroupsForm;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\RedirectException;
use \Exception;

class GroupsController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return ServersConstants::ROUTE_LIST;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $serverId
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function createAction(ServerRequestInterface $request, Response $response, $serverId)
    {
        $server = $this->getServer($request, $response, $serverId);
        $group = $this->getGroup($request, $response, null, $server);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $server->name,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id])
            )
            ->add(
                $this->getTranslate('admin_servers', 'groups'),
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'groups'])
            )
            ->add($this->getTranslate('labels', 'create'));
        
        $form = new GroupsForm($group);
        if ($this->processForm($request, $form)) {
        	$this->reloadPrivileges($server);
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersConstants::ROUTE_VIEW, [
                'server' => $server->id
            ], ['tab' => 'groups']);
        }
        
        return $this->getView()->render($response, 'admin/servers/groups/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $serverId
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function editAction(ServerRequestInterface $request, Response $response, $serverId, $id)
    {
        $server = $this->getServer($request, $response, $serverId);
        $group = $this->getGroup($request, $response, $id);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $server->name,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id])
            )
            ->add(
                $this->getTranslate('admin_servers', 'groups'),
	            $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'groups'])
            )
            ->add(
                $group->title,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'groups'])
            )
            ->add($this->getTranslate('labels', 'edit'));
        
        $form = new GroupsForm($group);
        if ($this->processForm($request, $form)) {
	        $this->reloadPrivileges($server);
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
	        return $this->redirect(ServersConstants::ROUTE_VIEW, [
		        'server' => $server->id
	        ], ['tab' => 'groups']);
        }
        
        return $this->getView()->render($response, 'admin/servers/groups/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $serverId
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(ServerRequestInterface $request, Response $response, $serverId, $id)
    {
        $server = $this->getServer($request, $response, $serverId);
        $group = $this->getGroup($request, $response, $id);
        
        try {
            $group->delete();
	        $this->reloadPrivileges($server);
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }

	    return $this->redirect(ServersConstants::ROUTE_VIEW, [
		    'server' => $server->id
	    ], ['tab' => 'groups']);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $serverId
     * @return Response
     * @throws NotFoundException
     */
    public function priorityAction(ServerRequestInterface $request, Response $response, $serverId)
    {
        $server = $this->getServer($request, $response, $serverId);
        $body = $request->getParsedBody();
    
        /** @var \Illuminate\Database\Connection|null $connection */
        $connection = $this->getContainer('db')->getConnection();
        $connection->beginTransaction();
        try {
            if (isset($body['priority']) && is_array($body['priority'])) {
                foreach ($body['priority'] as $priority => $groupId) {
                    $group = Group::find($groupId);
                    if ($group && $group->server_id = $server->id) {
                        $group->priority = $priority;
                        $group->save();
                    }
                }
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $response->withJson([
            'success' => true,
            'csrf' => $this->getCSRFToken()
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $id
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(ServerRequestInterface $request, Response $response, $id)
    {
        $server = Server::find($id);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param int $id
     * @param Server $server
     * @return Group
     * @throws NotFoundException
     */
    protected function getGroup(ServerRequestInterface $request, Response $response, $id = null, Server $server = null)
    {
        if ($id === null) {
            return new Group([
                'server_id' => $server->id
            ]);
        }

        $group = Group::find($id);
        if (!$group) {
            throw new NotFoundException($request, $response);
        }
        
        
        return $group;
    }

	/**
	 * @param Server $server
	 */
    protected function reloadPrivileges(Server $server)
    {
    	$this->getContainer('utils_rcon_exec')->reloadPrivileges($server);
    }
}
