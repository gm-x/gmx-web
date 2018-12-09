<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Permissions;
use \GameX\Forms\Admin\PlayersForm;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Player;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\UsersConstants;
use \GameX\Constants\Admin\PunishmentsConstants;
use \Slim\Exception\NotFoundException;
use \Exception;

class PlayersController extends BaseAdminController {

	protected function getActiveMenu() {
		return PlayersConstants::ROUTE_LIST;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(Request $request, Response $response, array $args = []) {
    	$filter = array_key_exists('filter', $_GET) && !empty($_GET['filter']) ? $_GET['filter'] : null;
    	$players = $filter === null
			? Player::get()
			: Player::filterCollection($filter)->get();

        $pagination = new Pagination($players, $request);
        return $this->render('admin/players/index.twig', [
            'players' => $pagination->getCollection(),
            'pagination' => $pagination,
			'filter' => $filter
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function viewAction(Request $request, Response $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        
        return $this->render('admin/players/view.twig', [
            'player' => $player,
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
	public function createAction(Request $request, Response $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PlayersConstants::ROUTE_EDIT, [
                'player' => $player->id,
            ]);
        }

		return $this->render('admin/players/form.twig', [
			'form' => $form->getForm(),
			'create' => true,
		]);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
	public function editAction(Request $request, Response $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PlayersConstants::ROUTE_EDIT, [
                'player' => $player->id,
            ]);
        }

		return $this->render('admin/players/form.twig', [
			'form' => $form->getForm(),
			'create' => false,
		]);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
	public function deleteAction(Request $request, Response $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);

		try {
			$player->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
		} catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
		}

		return $this->redirect(PlayersConstants::ROUTE_LIST);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Player
     * @throws NotFoundException
     */
    protected function getPlayer(Request $request, Response $response, array $args) {
		if (!array_key_exists('player', $args)) {
			return new Player();
		}

		$player = Player::find($args['player']);
		if (!$player) {
			throw new NotFoundException($request, $response);
		}

		return $player;
    }
}
