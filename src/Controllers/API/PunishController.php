<?php

namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \GameX\Models\Reason;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\SteamID;
use \GameX\Core\Validate\Rules\Callback;
use \GameX\Core\Exceptions\ApiException;

class PunishController extends BaseApiController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ApiException
     */
    public function indexAction(Request $request, Response $response)
    {
        $serverId = $this->getServer($request)->id;
        
        $playerExists = function ($value, array $values) {
            return Player::where('id', $value)->exists() ? $value : null;
        };
        
        $punisherExists = function ($value, array $values) {
            return ($value == 0 || Player::where('id', $value)->exists()) ? $value : null;
        };
        
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('player_id', true, [
                new Number(1),
                new Callback($playerExists)
            ])->set('punisher_id', true, [
                new Number(0),
                new Callback($punisherExists)
            ])->set('type', true, [
                new Number(0),
            ])->set('reason', true)->set('details', false)->set('time', true, [
                new Number(0)
            ]);
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ApiException($result->getFirstError(), ApiException::ERROR_VALIDATION);
        }
        
        $reason = $this->getReason($serverId, $result->getValue('reason'));
        
        $time = $result->getValue('time');
        $punisherId = $result->getValue('punisher_id');
        
        $punishment = new Punishment([
            'player_id' => $result->getValue('player_id'),
            'punisher_id' => $punisherId > 0 ? $punisherId : null,
            'server_id' => $serverId,
            'type' => $result->getValue('type'),
            'reason_id' => $reason->id,
            'details' => $result->getValue('details'),
            'expired_at' => $time > 0 ? time() + ($time * 60) : null,
            'status' => Punishment::STATUS_PUNISHED
        ]);
        $punishment->save();
        return $response->withStatus(200)->withJson([
            'success' => true,
            'punishment' => $punishment,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ApiException
     */
    public function immediatelyAction(Request $request, Response $response)
    {
        $serverId = $this->getServer($request)->id;
        
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('nick', true)->set('emulator', true, [
                new Number()
            ])->set('steamid', true, [
                new SteamID()
            ])->set('ip', true, [
                new IPv4()
            ])->set('type', true, [
                new Number(0),
            ])->set('reason', true)->set('details', false)->set('time', true, [
                new Number(0)
            ]);
        
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }
        
        $player = $this->getPlayer($result->getValue('steamid'), $result->getValue('emulator'),
            $result->getValue('nick'));
        
        $reason = $this->getReason($serverId, $result->getValue('reason'));
        
        $time = $result->getValue('time');
        
        $punishment = new Punishment([
            'player_id' => $player->id,
            'punisher_id' => null,
            'server_id' => $serverId,
            'type' => $result->getValue('type'),
            'reason_id' => $reason->id,
            'details' => $result->getValue('details'),
            'expired_at' => $time > 0 ? time() + ($time * 60) : null,
            'status' => Punishment::STATUS_PUNISHED
        ]);
        $punishment->save();
        
        return $response->withStatus(200)->withJson([
            'success' => true,
            'player' => $player,
            'punishment' => $punishment,
        ]);
    }
    
    /**
     * @param $serverId
     * @param $title
     * @return Reason
     */
    protected function getReason($serverId, $title)
    {
        return Reason::firstOrCreate([
            'server_id' => $serverId,
            'title' => $title
        ], [
            'server_id' => $serverId,
            'title' => $title,
            'time' => null,
            'overall' => 0,
            'menu' => 0,
            'active' => 1
        ]);
    }
    
    /**
     * @param $steamId
     * @param $emulator
     * @param $mick
     * @return Player
     */
    protected function getPlayer($steamId, $emulator, $mick)
    {
        return Player::firstOrCreate([
            'steamid' => $steamId,
            'emulator' => $emulator
        ], [
            'steamid' => $steamId,
            'emulator' => $emulator,
            'nick' => $mick,
            'auth_type' => Player::AUTH_TYPE_STEAM,
        ]);
    }
}
