<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Server;
use \GameX\Forms\Admin\ServersForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class ServersController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return ServersConstants::ROUTE_LIST;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function indexAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_menu', 'servers'));

        $pagination = new Pagination(Server::get(), $request);
        return $this->getView()->render($response, 'admin/servers/index.twig', [
            'servers' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param string|null $id
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function viewAction(Request $request, Response $response, $id = null)
    {
        $server = $this->getServer($request, $response, $id);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add($server->name);
    
        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');
        $players = $cache->get('players_online', $server);
        
        return $this->getView()->render($response, 'admin/servers/view.twig', [
            'server' => $server,
            'players' => $players,
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
        $server = $this->getServer($request, $response);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add($this->getTranslate('labels', 'create'));
        
        $form = new ServersForm($server);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersConstants::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/servers/form.twig', [
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param string|null $id
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(Request $request, Response $response, $id = null)
    {
        $server = $this->getServer($request, $response, $id);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $server->name,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id])
            )
            ->add($this->getTranslate('labels', 'edit'));
        
        $form = new ServersForm($server);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersConstants::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/servers/form.twig', [
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param string|null $id
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, $id = null)
    {
        $server = $this->getServer($request, $response, $id);
        
        try {
            $server->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(ServersConstants::ROUTE_LIST);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function tokenAction(Request $request, Response $response, array $args = [])
    {
        try {
            $server = $this->getServer($request, $response, $args);
            $server->token = $server->generateNewToken();
            $server->save();
            return $response->withJson([
                'success' => true,
                'token' => $server->token
            ]);
        } catch (Exception $e) {
            $this->getLogger()->exception($e);
            return $response->withJson([
                'success' => false,
                'error' => $this->getTranslate('labels', 'exception')
            ]);
        }
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param string|null $id
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, $id = null)
    {
        if ($id === null) {
            $id = $request->getParam('server');
        }
        
        if ($id === null) {
            return new Server();
        }
        
        $server = Server::find($id);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
}
