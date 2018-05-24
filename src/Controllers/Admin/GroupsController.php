<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Models\Group;
use \GameX\Core\BaseAdminController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Forms\Form;
use \GameX\Core\AccessFlags\Helper;
use \Exception;

class GroupsController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveAdminMenu() {
		return 'admin_servers_list';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
		$pagination = new Pagination($server->groups()->get(), $request);
		return $this->render('admin/servers/groups/index.twig', [
            'server' => $server,
			'groups' => $pagination->getCollection(),
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
        $group = $this->getGroup($request, $response, $args);
        $form = $this
            ->getForm($group)
            ->setAction((string)$request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $group->server_id = $server->id;
                    $group->title = $form->getValue('title');
                    $group->flags = Helper::readFlags($form->getValue('flags'));
                    $group->save();
                    return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/servers/groups/form.twig', [
            'server' => $server,
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
        $group = $this->getGroup($request, $response, $args);
        $form = $this
            ->getForm($group)
            ->setAction((string)$request->getUri())
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $group->title = $form->getValue('title');
                    $group->flags = Helper::readFlags($form->getValue('flags'));
                    $server->save();
                    return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/servers/groups/form.twig', [
            'server' => $server,
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
        $group = $this->getGroup($request, $response, $args);

        try {
            $group->delete();
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }

        return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
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
     * @return Group
     * @throws NotFoundException
     */
	protected function getGroup(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('group', $args)) {
            return new Group();
        }

        $group = Group::find($args['group']);
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        return $group;
    }

    /**
     * @param Group $group
     * @return Form
     */
    protected function getForm(Group $group) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_server_group');
        $form
            ->add('title', $group->title, [
                'type' => 'text',
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 1])
            ->add('flags', Helper::getFlags($group->flags), [
                'type' => 'text',
                'title' => 'Flags',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim']);

        return $form;
    }
}
