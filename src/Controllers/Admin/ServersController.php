<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use GameX\Models\Player;
use GameX\Models\Privilege;
use Illuminate\Database\Eloquent\Collection;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Server;
use \GameX\Forms\Admin\ServersForm;
use \GameX\Core\Auth\Permissions;
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
     * @param int $id
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

	    $sessions = $server->getActiveSessions();

        $groups = $server->groups()->orderBy('priority', 'asc')->get();

	    $privileges = $server->groups()
		    ->get()
		    ->reduce(function ($privileges, $item) {
		    	if ($privileges === null) {
				    $privileges = new Collection();
			    }
			    return $privileges->merge($item->players);
		    });

        return $this->getView()->render($response, 'admin/servers/view.twig', [
	        'tab' => $request->getParam('tab', 'online'),
            'server' => $server,
            'sessions' => $sessions,
	        'groups' => $groups,
	        'privileges' => $privileges
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
        
        $form = new ServersForm($server, true);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersConstants::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/servers/form.twig', [
            'form' => $form->getForm(),
            'create' => true,
	        'rconEnabled' => true,
        ]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param $id
	 * @return ResponseInterface
	 * @throws NotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 * @throws \GameX\Core\Exceptions\RedirectException
	 * @throws \GameX\Core\Exceptions\RoleNotFoundException
	 */
    public function editAction(Request $request, Response $response, $id)
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

	    $rconEnabled = $this->getPermissions()->hasUserAccessToResource(
		    ServersConstants::PERMISSION_RCON_GROUP,
		    ServersConstants::PERMISSION_RCON_KEY,
		    $server->id,
		    Permissions::ACCESS_EDIT
	    );

	    $form = new ServersForm($server, $rconEnabled);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersConstants::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/servers/form.twig', [
            'form' => $form->getForm(),
            'create' => false,
	        'rconEnabled' => $rconEnabled,
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
     * @param int $id
     * @return ResponseInterface
     */
    public function tokenAction(Request $request, Response $response, $id)
    {
        try {
            $server = $this->getServer($request, $response, $id);
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
