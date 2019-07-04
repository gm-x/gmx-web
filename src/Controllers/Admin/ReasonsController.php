<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Reason;
use \GameX\Models\Server;
use \GameX\Forms\Admin\ReasonsForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class ReasonsController extends BaseAdminController
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
     * @param ResponseInterface $response
     * @param int $serverId
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, $serverId)
    {
        $server = $this->getServer($request, $response, $serverId);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $server->name,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id])
            )
            ->add($this->getTranslate('admin_servers', 'reasons'));

        $pagination = new Pagination($server->reasons()->get(), $request);
        return $this->getView()->render($response, 'admin/servers/reasons/index.twig', [
            'server' => $server,
            'reasons' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $serverId
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, $serverId)
    {
        $server = $this->getServer($request, $response, $serverId);
        $reason = $this->getReason($request, $response, null, $server);

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
                $this->getTranslate('admin_servers', 'reasons'),
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'reasons'])
            )
            ->add($this->getTranslate('labels', 'create'));
        
        $form = new ReasonsForm($reason);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
	        return $this->redirect(ServersConstants::ROUTE_VIEW, [
		        'server' => $server->id
	        ], ['tab' => 'reasons']);
        }
        
        return $this->getView()->render($response, 'admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $serverId
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, $serverId, $id)
    {
        $server = $this->getServer($request, $response, $serverId);
        $reason = $this->getReason($request, $response, $id);

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
	            $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'reasons'])
            )
            ->add(
                $reason->title,
	            $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'reasons'])
            )
            ->add($this->getTranslate('labels', 'edit'));
        
        $form = new ReasonsForm($reason);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
	        return $this->redirect(ServersConstants::ROUTE_VIEW, [
		        'server' => $server->id
	        ], ['tab' => 'reasons']);
        }
        
        return $this->getView()->render($response, 'admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $serverId
     * @param int $id
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, $serverId, $id)
    {
        $server = $this->getServer($request, $response, $serverId);
        $group = $this->getReason($request, $response, $id);
        
        try {
            $group->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }

	    return $this->redirect(ServersConstants::ROUTE_VIEW, [
		    'server' => $server->id
	    ], ['tab' => 'reasons']);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $id
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(ServerRequestInterface $request, ResponseInterface $response, $id)
    {
        $server = Server::find($id);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $id
     * @param Server $server
     * @return Reason
     * @throws NotFoundException
     */
    protected function getReason(ServerRequestInterface $request, ResponseInterface $response, $id = null, Server $server = null) {
        if ($id === null) {
            return new Reason([
                'server_id' => $server->id
            ]);
        }

        $reason = Reason::find($id);
        if (!$reason) {
            throw new NotFoundException($request, $response);
        }
        
        
        return $reason;
    }
}
