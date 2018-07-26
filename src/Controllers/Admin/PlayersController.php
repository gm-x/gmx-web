<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \GameX\Models\Player;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Forms\Admin\PlayersForm;
use \GameX\Core\Pagination\Pagination;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;
use \Exception;

class PlayersController extends BaseAdminController {

	protected function getActiveMenu() {
		return 'admin_players_list';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
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
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
		$player = $this->getPlayer($request, $response, $args);
        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_players_edit', [
                'player' => $player->id,
            ]);
        }

		return $this->render('admin/players/form.twig', [
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
		$player = $this->getPlayer($request, $response, $args);
        $form = new PlayersForm($player);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_players_edit', [
                'player' => $player->id,
            ]);
        }

		return $this->render('admin/players/form.twig', [
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
		$player = $this->getPlayer($request, $response, $args);

		try {
			$player->delete();
            $this->addSuccessMessage($this->getTranslate('admins_players', 'removed'));
		} catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
			/** @var \Monolog\Logger $logger */
			$logger = $this->getContainer('log');
			$logger->error((string) $e);
		}

		return $this->redirect('admin_players_list');
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
}
