<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use GameX\Models\Punishment;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Server;
use \GameX\Forms\Admin\PunishmentsForm;
use \GameX\Constants\Admin\PunishmentsConstants;
use \Slim\Exception\NotFoundException;

class PunishmentsController extends BaseAdminController {

    /**
     * @return string
     */
    protected function getActiveMenu() {
        return PunishmentsConstants::ROUTE_LIST;
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
        return $this->render('admin/players/punishments/index.twig', []);
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
        $punishment = $this->getPrivilege($request, $response, $args, $player);
    
        $form = new PunishmentsForm($server, $punishment);
        try {
            if ($this->processForm($request, $form)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect(PunishmentsConstants::ROUTE_EDIT, [
                    'player' => $player->id,
                    'privilege' => $punishment->id,
                ]);
            }
        } catch (\GameX\Core\Exceptions\PrivilegeFormException $e) {
            $this->addErrorMessage('Add privileges groups before adding privilege');
            return $this->redirect(\GameX\Constants\Admin\GroupsConstants::ROUTE_LIST, ['server' => $server->id]);
        }
        
        return $this->render('admin/players/punishments/index.twig', []);
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
     * @return Punishment
     * @throws NotFoundException
     */
    protected function getPunishment(Request $request, Response $response, array $args, Player $player) {
        if (!array_key_exists('punishment', $args)) {
            return new Punishment([
                'player_id' => $player->id
            ]);
        }
    
        $punishment = Punishment::find($args['punishment']);
        if (!$punishment) {
            throw new NotFoundException($request, $response);
        }
        
        return $punishment;
    }
}
