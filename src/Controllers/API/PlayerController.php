<?php

namespace GameX\Controllers\API;

use \Carbon\Carbon;
use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Player;
use \GameX\Models\PlayerSession;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\SteamID;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Exceptions\ValidationException;

class PlayerController extends BaseApiController
{
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function connectAction(Request $request, Response $response)
    {
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
            ])
            ->set('session_id', false, [
                new Number(1)
            ]);
        
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }
        
        $server = $this->getServer($request);
        $session = null;
	    $now = Carbon::now();
        
        /** @var Player|null $player */
        $player = Player::query()->when($result->getValue('id'), function ($query) use ($result) {
                $query->where('id', $result->getValue('id'));
            })->orWhere(function ($query) use ($result) {
                $query->whereIn('auth_type', [
                        Player::AUTH_TYPE_STEAM,
                        Player::AUTH_TYPE_STEAM_AND_PASS,
                        Player::AUTH_TYPE_STEAM_AND_HASH,
                    ])->where([
                        'emulator' => $result->getValue('emulator'),
                        'steamid' => $result->getValue('steamid')
                    ]);
            })->orWhere(function ($query) use ($result) {
                $query->whereIn('auth_type', [
                        Player::AUTH_TYPE_NICK_AND_PASS,
                        Player::AUTH_TYPE_NICK_AND_HASH,
                    ])->where([
                        'nick' => $result->getValue('nick')
                    ]);
            })->first();
        
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
            $player->save();
        } else if($result->getValue('session_id')) {
            $session = PlayerSession::find($result->getValue('session_id'));
        } else {
            $session = $player->getActiveSession();
        }
        
        if (!$session) {
            $session = new PlayerSession();
            $session->fill([
                'player_id' => $player->id,
                'server_id' => $server->id,
                'status' => PlayerSession::STATUS_ONLINE,
                'disconnected_at' => null,
	            'ping_at' => $now,
            ]);
        } elseif ($session->server_id != $server->id) {
            $session->status = PlayerSession::STATUS_OFFLINE;
            $session->disconnected_at = $now;
            $session->save();

	        $session = new PlayerSession();
            $session->fill([
                'player_id' => $player->id,
                'server_id' => $server->id,
                'status' => PlayerSession::STATUS_ONLINE,
                'disconnected_at' => null,
                'ping_at' => $now,
            ]);
        } else {
            $session->ping_at = $now;
            $session->updated_at = $now;
        }
        
        $session->save();
        
        $punishments = $player->getActivePunishments($server);
    
        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');
        $cache->clear('players_online', $server);
        
        return $this->response($response, 200, [
            'success' => true,
            'player_id' => $player->id,
            'session_id' => $session->id,
            'user_id' => $player->user ? $player->user->id : null,
            'punishments' => $punishments,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function disconnectAction(Request $request, Response $response)
    {
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('session_id', true, [
                new Number(1)
            ]);
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }

        $session = PlayerSession::find($result->getValue('session_id'));
        if ($session) {
            $session->status = PlayerSession::STATUS_OFFLINE;
            $session->disconnected_at = Carbon::now();
            $session->save();
        }

        $server = $this->getServer($request);

        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');
        $cache->clear('players_online', $server);
        
        return $this->response($response, 200, [
            'success' => true,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     */
    public function assignAction(Request $request, Response $response)
    {
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('id', true, [
                new Number(1)
            ])->set('token', true, [
                new Length(32, 32)
            ]);
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }
        $player = Player::find($result->getValue('id'), ['id', 'user_id']);
        if (!$player) {
            throw new ValidationException('Player not found');
        }
        if ($player->user_id !== null) {
            throw new ValidationException('User already assigned');
        }
        $user = UserModel::where('token', $result->getValue('token'))->first(['id']);
        if (!$user) {
            throw new ValidationException('Invalid user token');
        }
        
        $player->user_id = $user->id;
        $player->save();
        
        return $this->response($response, 200, [
            'success' => true,
            'user_id' => $user->id
        ]);
    }
}
