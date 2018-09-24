<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Punishment;
use \GameX\Models\Player;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PunishmentsForm;
use \GameX\Constants\Admin\PunishmentsConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Constants\Admin\PlayersConstants;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\PunishmentsFormException;

class PunishmentsController extends BaseAdminController {

    /**
     * @return string
     */
    protected function getActiveMenu() {
        return PlayersConstants::ROUTE_LIST;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(Request $request, Response $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
        return $this->render('admin/players/punishments/index.twig', [
            'player' => $player
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
        $server = $this->getServer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player, $server);
    
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PunishmentsConstants::ROUTE_EDIT, [
                    'player' => $player->id,
                    'privilege' => $punishment->id,
                ]);
            }
        } catch (PunishmentsFormException $e) {
            $this->addErrorMessage('Add reasons before punish player');
            return $this->redirect(ReasonsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->render('admin/players/punishments/form.twig', [
            'player' => $player,
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
        $server = $this->getServer($request, $response, $args);
        $punishment = $this->getPunishment($request, $response, $args, $player, $server);
        
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PunishmentsConstants::ROUTE_EDIT, [
                    'player' => $player->id,
                    'privilege' => $punishment->id,
                ]);
            }
        } catch (PunishmentsFormException $e) {
            $this->addErrorMessage('Add reasons before punish player');
            return $this->redirect(ReasonsConstants::ROUTE_CREATE, ['server' => $server->id]);
        }
        
        return $this->render('admin/players/punishments/form.twig', [
            'player' => $player,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param bool $withPunishments
     * @return Player
     * @throws NotFoundException
     */
    protected function getPlayer(Request $request, Response $response, array $args, $withPunishments = false) {
        if (!array_key_exists('player', $args)) {
            return new Player();
        }
        
        $player = $withPunishments
            ? Player::with('punishments')->find($args['player'])
            : Player::find($args['player']);
        
        if (!$player) {
            throw new NotFoundException($request, $response);
        }
        
        return $player;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(Request $request, Response $response, array $args) {
        $server = Server::find($args['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param Player $player
     * @param Server $server
     * @return Punishment
     * @throws NotFoundException
     */
    protected function getPunishment(Request $request, Response $response, array $args, Player $player, Server $server) {
        if (!array_key_exists('punishment', $args)) {
            return new Punishment([
                'player_id' => $player->id,
                'punisher_id' => null,
                'punisher_user_id' => $this->getUser()->id,
                'server_id' => $server->id
            ]);
        }
    
        $punishment = Punishment::find($args['punishment']);
        if (!$punishment) {
            throw new NotFoundException($request, $response);
        }
        
        return $punishment;
    }
}
