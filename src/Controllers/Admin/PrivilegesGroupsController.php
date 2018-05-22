<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Models\PrivilegesGroups;
use \GameX\Core\BaseController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Exception\NotFoundException;
use \Exception;

class PrivilegesGroupsController extends BaseController {

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
                    $group->fill($form->getValues());
                    $group->save();
                    return $this->redirect('admin_servers_list');
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
     * @return PrivilegesGroups
     * @throws NotFoundException
     */
	protected function getGroup(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('group', $args)) {
            return new PrivilegesGroups();
        }

        $group = PrivilegesGroups::find($args['group']);
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        return $group;
    }

    /**
     * @param PrivilegesGroups $group
     * @return Form
     */
    protected function getForm(PrivilegesGroups $group) {
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
            ->add('flags', $group->flags, [
                'type' => 'number',
                'title' => 'Flags',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'integer']);

        

        return $form;
    }
}
