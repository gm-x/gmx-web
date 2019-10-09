<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Access;
use \GameX\Models\Server;
use \GameX\Forms\Admin\AccessForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class AccessController extends BaseAdminController
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
	 * @throws \GameX\Core\Exceptions\RedirectException
	 */
	public function createAction(ServerRequestInterface $request, ResponseInterface $response, $serverId)
	{
		$server = $this->getServer($request, $response, $serverId);
		$access = $this->getAccess($request, $response, null, $server);

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
				$this->getTranslate('admin_servers', 'access'),
				$this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'access'])
			)
			->add($this->getTranslate('labels', 'create'));

		$form = new AccessForm($access);
		if ($this->processForm($request, $form)) {
			$this->addSuccessMessage($this->getTranslate('labels', 'saved'));
			return $this->redirect(ServersConstants::ROUTE_VIEW, [
				'server' => $server->id
			], ['tab' => 'access']);
		}

		return $this->getView()->render($response, 'admin/servers/access/form.twig', [
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
		$access = $this->getAccess($request, $response, $id);

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
				$this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'access'])
			)
			->add(
				$access->description,
				$this->pathFor(ServersConstants::ROUTE_VIEW, ['server' => $server->id], ['tab' => 'access'])
			)
			->add($this->getTranslate('labels', 'edit'));

		$form = new AccessForm($access);
		if ($this->processForm($request, $form)) {
			$this->addSuccessMessage($this->getTranslate('labels', 'saved'));
			return $this->redirect(ServersConstants::ROUTE_VIEW, [
				'server' => $server->id
			], ['tab' => 'access']);
		}

		return $this->getView()->render($response, 'admin/servers/access/form.twig', [
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
		$access = $this->getAccess($request, $response, $id);

		try {
			$access->delete();
			$this->addSuccessMessage($this->getTranslate('labels', 'removed'));
		} catch (Exception $e) {
			$this->addErrorMessage($this->getTranslate('labels', 'exception'));
			$this->getLogger()->exception($e);
		}

		return $this->redirect(ServersConstants::ROUTE_VIEW, [
			'server' => $server->id
		], ['tab' => 'access']);
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
	 * @return Access
	 * @throws NotFoundException
	 */
	protected function getAccess(ServerRequestInterface $request, ResponseInterface $response, $id = null, Server $server = null) {
		if ($id === null) {
			return new Access([
				'server_id' => $server->id
			]);
		}

		$access = Access::find($id);
		if (!$access) {
			throw new NotFoundException($request, $response);
		}


		return $access;
	}
}
