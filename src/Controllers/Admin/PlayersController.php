<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \GameX\Models\Player;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\FormInputPassword;
use \GameX\Core\Forms\Elements\FormInputText;
use \GameX\Core\Forms\Elements\FormSelect;
use \Form\Validator;
use \Exception;

class PlayersController extends BaseAdminController {

	protected function getActiveAdminMenu() {
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
		$form = $this
			->getForm($player)
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$player->fill($form->getValues());
					$player->save();
					return $this->redirect('admin_players_list');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/players/form.twig', [
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
		$player = $this->getPlayer($request, $response, $args);
		$form = $this
			->getForm($player)
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$player->fill($form->getValues());
					$player->save();
					return $this->redirect('admin_players_list');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/players/form.twig', [
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
		$player = $this->getPlayer($request, $response, $args);

		try {
			$player->delete();
		} catch (Exception $e) {
			$this->addFlashMessage('error', 'Something wrong. Please Try again later.');
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

	/**
	 * @param Player $player
	 * @return Form
	 */
    protected function getForm(Player $player) {
    	$checkPasswordRequired = function ($password, Validator $validator) {
			return $validator->getValue('auth_type') !== Player::AUTH_TYPE_STEAM  && empty($password) ? false : true;
		};

		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_player');
		$form
			->add(new FormInputText('steamid', $player->steamid, [
				'title' => 'Steam ID',
				'error' => 'Valid STEAM ID',
				'required' => true,
			]))
			->add(new FormInputText('nick', $player->nick, [
				'title' => 'Nickname',
				'error' => 'Required',
				'required' => true,
			]))
			->add(new FormSelect('auth_type', $player->auth_type, [
				'title' => 'Auth Type',
				'error' => 'Required',
				'required' => true,
				'empty_option' => 'Choose auth type',
				'options' => [
					Player::AUTH_TYPE_STEAM => 'Steam ID',
					Player::AUTH_TYPE_STEAM_AND_PASS => 'Steam ID + pass',
					Player::AUTH_TYPE_NICK_AND_PASS => 'Nick + pass',
					Player::AUTH_TYPE_STEAM_AND_HASH => 'Steam ID + hash',
					Player::AUTH_TYPE_NICK_AND_HASH => 'Nick + hash',
				]
			]))
			->add(new FormInputPassword('password', '', [
				'title' => 'Password',
				'error' => 'Required for pass or hash',
				'required' => false,
			]))
			->setRules('steamid', ['required', 'trim', 'min_length' => 1, 'regexp' => '/^(?:STEAM|VALVE)_\d:\d:\d+$/'])
			->setRules('nick', ['required', 'trim', 'min_length' => 1])
			->setRules('auth_type', ['required', 'trim', 'min_length' => 1, 'in' => [
				Player::AUTH_TYPE_STEAM ,
				Player::AUTH_TYPE_STEAM_AND_PASS,
				Player::AUTH_TYPE_NICK_AND_PASS,
				Player::AUTH_TYPE_STEAM_AND_HASH,
				Player::AUTH_TYPE_NICK_AND_HASH,
			]])
			->setRules('password', ['check' => $checkPasswordRequired]);

		if (!$player->exists) {
			$form->addRules('steamid', ['exists' => function ($steamid, Validator $validator) {
				return !Player::where('steamid', '=', $steamid)->exists();
			}]);
		}

		return $form;
	}
}
