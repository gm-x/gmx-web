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

class ReasonsController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return ServersConstants::ROUTE_LIST;
	}

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $pagination = new Pagination($server->reasons()->get(), $request);
        return $this->render('admin/servers/reasons/index.twig', [
            'server' => $server,
            'reasons' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $reason = $this->getReason($request, $response, $args, $server);
        
        $form = new ReasonsForm($reason);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ReasonsConstants::ROUTE_EDIT, [
                'server' => $server->id,
                'reason' => $reason->id
            ]);
        }
        
        return $this->render('admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $reason = $this->getReason($request, $response, $args, $server);
        
        $form = new ReasonsForm($reason);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ReasonsConstants::ROUTE_EDIT, [
                'server' => $server->id,
                'reason' => $reason->id
            ]);
        }
        
        return $this->render('admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getReason($request, $response, $args, $server);
        
        try {
            $group->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(ReasonsConstants::ROUTE_LIST, ['server' => $server->id]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('server', $args)) {
            return new Server();
        }
        
        $server = Server::find($args['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @param Server $server
     * @return Reason
     * @throws NotFoundException
     */
    protected function getReason(ServerRequestInterface $request, ResponseInterface $response, array $args, Server $server) {
        if (!array_key_exists('reason', $args)) {
            $reason = new Reason();
            $reason->server_id = $server->id;
        } else {
            $reason = Reason::find($args['reason']);
        }
    
        if (!$reason) {
            throw new NotFoundException($request, $response);
        }
    
    
        return $reason;
    }
}
