<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Core\BaseController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \Slim\Exception\NotFoundException;
use \Exception;

class ServersController extends BaseController {

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$pagination = new Pagination(Server::all(), $request);
		return $this->render('admin/servers/index.twig', [
			'servers' => $pagination->getCollection(),
			'pagination' => $pagination,
		]);
    }

	public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_servers_create');
		$form
			->setAction($this->pathFor('admin_servers_create'))
			->add('name', '', [
				'type' => 'text',
				'title' => 'Name',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->add('ip', '', [
				'type' => 'text',
				'title' => 'IP',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->add('port', '', [
				'type' => 'number',
				'title' => 'Port',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->processRequest();

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$model = new Server();
					$model->fill($form->getValues());
					$model->save();
					return $this->redirect('admin_servers_list');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/servers/form.twig', [
			'form' => $form,
		]);
	}

	public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$server = $this->getServer($request, $response, $args);

		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_servers_list');
		$form
			->setAction($this->pathFor('admin_servers_edit', ['role' => $server->id]))
			->add('name', '', [
				'type' => 'text',
				'title' => 'Name',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->add('ip', '', [
				'type' => 'text',
				'title' => 'IP',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->add('port', '', [
				'type' => 'number',
				'title' => 'Port',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			], ['required', 'trim'])
			->processRequest();

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$server->fill($form->getValues());
					$server->save();
					return $this->redirect('admin_servers_list');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/roles/form.twig', [
			'form' => $form,
		]);
	}

	public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
//		$role = $this->getRole($request, $response, $args);

		try {
//			$role->delete();
		} catch (Exception $e) {
			$this->addFlashMessage('error', 'Something wrong. Please Try again later.');
			/** @var \Monolog\Logger $logger */
			$logger = $this->getContainer('log');
			$logger->error((string) $e);
		}

		return $this->redirect('admin_roles_list');
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return Server
	 * @throws NotFoundException
	 */
	protected function getServer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$server = Server::find($args['server']);
		if (!$server) {
			throw new NotFoundException($request, $response);
		}

		return $server;
	}
}
