<?php

namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \GameX\Models\Reason;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\SteamID;
use \GameX\Core\Validate\Rules\Callback;
use \GameX\Core\Exceptions\ValidationException;

class PunishController extends BaseApiController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
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
        $validator
	        ->set('player_id', true, [
                new Number(1),
                new Callback($playerExists)
            ])
	        ->set('punisher_id', true, [
                new Number(0),
                new Callback($punisherExists)
            ])
	        ->set('type', true)
	        ->set('extra', false, [
		        new Number(0)
	        ])
	        ->set('reason', true)
	        ->set('details', false)
	        ->set('time', true, [
                new Number(0)
            ]);
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }
        
        $reason = $this->getReason($serverId, $result->getValue('reason'));
        
        $time = $result->getValue('time');
        $punisherId = $result->getValue('punisher_id');
        
        $punishment = new Punishment([
            'player_id' => $result->getValue('player_id'),
            'punisher_id' => $punisherId > 0 ? $punisherId : null,
            'server_id' => $serverId,
            'type' => $result->getValue('type'),
            'extra' => $result->getValue('extra'),
            'reason_id' => $reason->id,
            'details' => $result->getValue('details'),
            'expired_at' => $time > 0 ? Carbon::now()->addSeconds($time) : null,
            'status' => Punishment::STATUS_PUNISHED
        ]);
        $punishment->save();
	    $punishment->load('reason')->makeVisible('reason');
        return $response->withStatus(200)->withJson([
            'success' => true,
            'punishment' => $punishment,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     */
    public function immediatelyAction(Request $request, Response $response)
    {
        $serverId = $this->getServer($request)->id;
        
        $validator = new Validator($this->getContainer('lang'));
        $validator
	        ->set('nick', true)
	        ->set('emulator', true, [
                new Number()
            ])
	        ->set('steamid', true, [
                new SteamID()
            ])
	        ->set('ip', true, [
                new IPv4()
            ])
	        ->set('type', true)
	        ->set('extra', false, [
		        new Number(0)
	        ])
	        ->set('reason', true)
	        ->set('details', false)
	        ->set('time', true, [
                new Number(0)
            ]);
        
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }
        
        $player = $this->getPlayer(
        	$result->getValue('steamid'),
	        $result->getValue('emulator'),
	        $result->getValue('nick')
        );
        
        $reason = $this->getReason($serverId, $result->getValue('reason'));
        $time = $result->getValue('time');
        
        $punishment = new Punishment([
            'player_id' => $player->id,
            'punisher_id' => null,
            'server_id' => $serverId,
            'type' => $result->getValue('type'),
	        'extra' => $result->getValue('extra'),
            'reason_id' => $reason->id,
            'details' => $result->getValue('details'),
            'expired_at' => $time > 0 ? Carbon::now()->addSeconds($time) : null,
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
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function reasonsAction(Request $request, Response $response)
	{
		$server = $this->getServer($request);
		$reasons = $server->reasons()->where([
			'active' => true,
			'menu' => true
		])->get();

		return $response->withStatus(200)->withJson([
			'success' => true,
			'reasons' => $reasons,
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
