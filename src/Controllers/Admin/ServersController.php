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
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(Request $request, Response $response, array $args = [])
    {
        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            );

        $pagination = new Pagination(Server::get(), $request);
        return $this->getView()->render($response, 'admin/servers/index.twig', [
            'servers' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function viewAction(Request $request, Response $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $server->name,
                $this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id])
            );
    
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
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(Request $request, Response $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);

        $this->getBreadcrumbs()
            ->add(
                $this->getTranslate('admin_menu', 'servers'),
                $this->pathFor(ServersConstants::ROUTE_LIST)
            )
            ->add(
                $this->getTranslate('labels', 'create'),
                $this->pathFor(ServersConstants::ROUTE_CREATE)
            );
        
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
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(Request $request, Response $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);

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
                $this->getTranslate('labels', 'edit'),
                $this->pathFor(ServersConstants::ROUTE_EDIT, ['server' => $server->id])
            );
        
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
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);
        
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
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, array $args)
    {
        if (array_key_exists('server', $args)) {
            $serverId = $args['server'];
        } else {
            $serverId = $request->getParam('server');
        }
        
        if (!$serverId) {
            return new Server();
        }
        
        $server = Server::find($serverId);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
}
