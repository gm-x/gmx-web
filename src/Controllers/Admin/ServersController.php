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
        $server = $this->getServer($request, $response, $args);
        $form = $this
            ->getForm($server)
            ->setAction((string)$request->getUri())
            ->processRequest($request);
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

		return $this->render('admin/servers/form.twig', [
			'form' => $form,
            'create' => true,
		]);
	}

	public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$server = $this->getServer($request, $response, $args);
        $form = $this
            ->getForm($server)
            ->setAction((string)$request->getUri())
            ->processRequest($request);
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

		return $this->render('admin/servers/form.twig', [
			'form' => $form,
            'create' => false,
		]);
	}

	public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);

		try {
			$server->delete();
		} catch (Exception $e) {
			$this->addFlashMessage('error', 'Something wrong. Please Try again later.');
			/** @var \Monolog\Logger $logger */
			$logger = $this->getContainer('log');
			$logger->error((string) $e);
		}

		return $this->redirect('admin_servers_list');
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

	protected function getForm(Server $server) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_server');
        $form
            ->add('name', $server->name, [
                'type' => 'text',
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 1])
            ->add('ip', $server->ip, [
                'type' => 'text',
                'title' => 'IP',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'ipv4'])
            ->add('port', $server->port, [
                'type' => 'number',
                'title' => 'Port',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'integer', 'between' => [1024, 65535]]);

        if (!$server->exists) {
            $form->addRules('port', ['exists' => function ($port, \Form\Validator $form) {
                return !Server::where(['ip' => $form->getValue('ip'), 'port' => $port])->exists();
            }]);
        }

        return $form;
    }
}
