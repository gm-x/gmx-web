<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Forms\Admin\Servers\CreateServerForm;
use \GameX\Forms\Admin\Servers\UpdateServerForm;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
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

		$form = new CreateServerForm($server, $this->getConfig('secret', ''));
		try {
			$form->create();

			if ($form->process($request)) {
			    $this->addSuccessMessage($this->getTranslate('admins_servers', 'created'));
				return $this->redirect('admin_servers_edit', ['server' => $server->id]);
			}
		} catch (FormException $e) {
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
		}

		return $this->render('admin/servers/form.twig', [
			'form' => $form->getForm(),
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
        
        $form = new UpdateServerForm($server);
		try {
            $form->create();
            
            if ($form->process($request)) {
                $this->addSuccessMessage($this->getTranslate('admins_servers', 'updated'));
                return $this->redirect('admin_servers_edit', ['server' => $server->id]);
            }
        } catch (FormException $e) {
            $form->getForm()->setError($e->getField(), $e->getMessage());
            return $this->redirectTo($form->getForm()->getAction());
        } catch (ValidationException $e) {
            if ($e->hasMessage()) {
                $this->addErrorMessage($e->getMessage());
            }
            return $this->redirectTo($form->getForm()->getAction());
        }

		return $this->render('admin/servers/form.twig', [
			'form' => $form->getForm(),
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
            $this->addSuccessMessage($this->getTranslate('admins_servers', 'removed'));
		} catch (Exception $e) {
			$this->addErrorMessage($this->getTranslate('admins_servers', 'exception'));
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
}
