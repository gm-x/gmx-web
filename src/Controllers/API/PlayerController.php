<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Rules\SteamID;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\IPv4;
use \GameX\Core\Forms\Rules\Length;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Player;
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
            ->set('id', false, [
                new Number(1)
            ])
            ->set('emulator', true, [
                new Number(0)
            ])
            ->set('steamid', true, [
                new SteamID()
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

        /** @var Player|null $player */
        $player = Player::query()
            ->when($result->getValue('id'), function ($query) use ($result) {
                $query->where('id', $result->getValue('id'));
            })
            ->orWhere(function ($query) use ($result) {
                $query
                    ->whereIn('auth_type', [
                        Player::AUTH_TYPE_STEAM,
                        Player::AUTH_TYPE_STEAM_AND_PASS,
                        Player::AUTH_TYPE_STEAM_AND_HASH,
                    ])
                    ->where([
                        'emulator' => $result->getValue('emulator'),
                        'steamid' => $result->getValue('steamid')
                    ]);
            })
            ->orWhere(function ($query) use ($result) {
                $query
                    ->whereIn('auth_type', [
                        Player::AUTH_TYPE_NICK_AND_PASS,
                        Player::AUTH_TYPE_NICK_AND_HASH,
                    ])
                    ->where([
                        'nick' => $result->getValue('nick')
                    ]);
            })
            ->first();

        if (!$player) {
            $player = new Player();
            $player->steamid = $result->getValue('steamid');
            $player->emulator = $result->getValue('emulator');
            $player->nick = $result->getValue('nick');
            $player->ip = $result->getValue('ip');
            $player->auth_type = Player::AUTH_TYPE_STEAM;
//        } else if ($player->getIsAuthByNick()) {
//            $player->emulator = $result->getValue('emulator');
//            $player->steamid = $result->getValue('steamid');
//        } else {
//            $player->nick = $result->getValue('nick');
        }

        $player->server_id = $server->id;
        $player->save();

        $server->num_players = Player::where('server_id', $server->id)->count();
        $server->save();

        $punishments = $player->getActivePunishments($server);

        return $this->response($response, 200, [
            'success' => true,
            'player_id' => $player->id,
            'user' => $player->user,
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

        return $this->response($response, 200, [
            'success' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function assignAction(Request $request, Response $response, array $args) {
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('id', true, [
                new Number(1)
            ])
            ->set('token', true, [
                new Length(32, 32)
            ]);

        $result = $validator->validate($this->getBody($request));

        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }
        $player = Player::find($result->getValue('id'), ['id', 'user_id']);
        if (!$player) {
            throw new ApiException('Player not found', ApiException::ERROR_VALIDATION);
        }
        if ($player->user_id !== null) {
            throw new ApiException('User already assigned', ApiException::ERROR_VALIDATION);
        }
        $user = UserModel::where('token', $result->getValue('token'))->first(['id']);
        if (!$user) {
            throw new ApiException('Invalid user token', ApiException::ERROR_VALIDATION);
        }

        $player->user_id = $user->id;
        $player->save();

        return $this->response($response, 200, [
            'success' => true,
        ]);
    }
}
