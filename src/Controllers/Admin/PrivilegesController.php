<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Group;
use \GameX\Core\BaseController;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Forms\Form;
use \GameX\Core\AccessFlags\Helper;
use \Exception;

class PrivilegesController extends BaseController {

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
		$pagination = new Pagination($player->privileges()->get(), $request);
		return $this->render('admin/players/privileges/index.twig', [
            'player' => $player,
			'privileges' => $pagination->getCollection(),
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
	 * @return Player
	 * @throws NotFoundException
	 */
	protected function getPlayer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
	    if (!array_key_exists('player', $args)) {
	        return new Player();
        }

		$player = Player::find($args['player']);
		if (!$player) {
			throw new NotFoundException($request, $response);
		}

		return $player;
	}

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Privilege
     * @throws NotFoundException
     */
	protected function getPrivilege(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('privilege', $args)) {
            return new Privilege();
        }

        $privilege = Privilege::find($args['privilege']);
        if (!$privilege) {
            throw new NotFoundException($request, $response);
        }

        return $privilege;
    }

    /**
     * @param Privilege $privilege
     * @return Form
     */
    protected function getForm(Privilege $privilege) {
        
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_server_group');
        $form
            ->add('group', $privilege->group_id, [
                'type' => 'select',
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
