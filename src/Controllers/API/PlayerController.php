<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Rules\SteamID;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\IPv4;
use \GameX\Core\Exceptions\ApiException;

class PlayerController extends BaseApiController {

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function connectAction(Request $request, Response $response, array $args) {
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('steamid', true, [
                new SteamID()
            ])
            ->set('emulator', true, [
                new Number()
            ])
            ->set('nick', true)
            ->set('ip', true, [
                new IPv4()
            ]);
            
    
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }
    
        $server = $this->getServer($request);

        // TODO: Find players where auth_type is by nick
        $player = Player::where([
            'steamid' => $result->getValue('steamid'),
            'emulator' => $result->getValue('emulator')
        ])->first();
        if (!$player) {
            $player = new Player();
            $player->steamid = $result->getValue('steamid');
            $player->emulator = $result->getValue('emulator');
            $player->nick = $result->getValue('nick');
            $player->ip = $result->getValue('ip');
            $player->auth_type = Player::AUTH_TYPE_STEAM;
            $player->server_id = $server->id;
        } else {
            if ($player->getIsAuthByNick()) {
                $player->nick = $result->getValue('nick');
            }
            $player->ip = $result->getValue('ip');
            $player->server_id = $server->id;
        }
        $player->save();
    
        $server->num_players = Player::where('server_id', $server->id)->count();
        $server->save();
        
        $punishments = $player->getActivePunishments($server);

        return $response->withStatus(200)->withJson([
            'success' => true,
            'player' => [
                'id' => $player->id,
            ],
            'user' => null,
            'punishments' => $punishments,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function disconnectAction(Request $request, Response $response, array $args) {
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('id', true, [
                new Number(1)
            ]);
    
    
        $result = $validator->validate($this->getBody($request));
    
        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }
        
        $server = $this->getServer($request);
    
        $player = Player::where('id', $result->getValue('id'))->first();
        $player->server_id = null;
        $player->save();
        
        $server->num_players = Player::where('server_id', $server->id)->count();
        $server->save();
    
        return $response->withStatus(200)->withJson([
            'success' => true,
        ]);
    }
}
