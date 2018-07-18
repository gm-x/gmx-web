<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Firebase\JWT\JWT;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number;
use \Slim\Exception\NotFoundException;
use \Exception;

class ServersController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_servers_list';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$pagination = new Pagination(Server::get(), $request);
		return $this->render('admin/servers/index.twig', [
			'servers' => $pagination->getCollection(),
			'pagination' => $pagination,
		]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
	public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $form = $this
            ->getForm($server)
            ->setAction($request->getUri())
            ->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
                    $server->fill($form->getValues());
                    $server->save();
					$server->token = JWT::encode([
						'server_id' => $server->id
					], $this->getConfig('secret', ''), 'HS512');
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
	public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$server = $this->getServer($request, $response, $args);
        $form = $this
            ->getForm($server)
            ->setAction($request->getUri())
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
	public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);

		try {
			$server->delete();
		} catch (Exception $e) {
			$this->addErrorMessage('Something wrong. Please Try again later.');
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

    /**
     * @param Server $server
     * @return Form
     */
	protected function getForm(Server $server) {
        $form = $this->createForm('admin_server')
            ->add(new Text('name', $server->name, [
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
            ->add(new Text('ip', $server->ip, [
                'title' => 'IP',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
            ->add(new Number('port', $server->port, [
                'title' => 'Port',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
			->setRules('name', ['required', 'trim', 'min_length' => 1])
			->setRules('ip', ['required', 'ipv4'])
			->setRules('port', ['required', 'integer', 'between' => [1024, 65535]]);

        if (!$server->exists) {
            $form->addRules('port', ['exists' => function ($port, \Form\Validator $form) {
                return !Server::where(['ip' => $form->getValue('ip'), 'port' => $port])->exists();
            }]);
        }

        return $form;
    }
}
