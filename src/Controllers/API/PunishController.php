<?php

namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Server;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \GameX\Models\Reason;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\SteamID;
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
		$validator = new Validator($this->getContainer('lang'));
        $validator
	        ->set('player_id', true, [
                new Number(1),
            ])
	        ->set('punisher_id', true, [
                new Number(0),
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
            throw new ValidationException($result->getFirstError(), 0);
        }

        $player = Player::where('id', $result->getValue('player_id'))->first();
	    if (!$player) {
		    throw new ValidationException('Player with id ' . $result->getValue('player_id') . ' not found!', 1);
	    }

        $server = $this->getServer($request);
		$reason = $this->getReason($server->id, $result->getValue('reason'));

	    $punishment = $this->punishPlayer(
		    $server,
		    $player,
		    $result->getValue('type'),
		    $result->getValue('extra'),
		    $result->getValue('punisher_id'),
		    $reason,
		    $result->getValue('details'),
		    $result->getValue('time')
	    );

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
            throw new ValidationException($result->getFirstError(), 0);
        }
        
        $player = $this->getPlayer(
        	$result->getValue('steamid'),
	        $result->getValue('emulator'),
	        $result->getValue('nick')
        );

	    $server = $this->getServer($request);
		$reason = $this->getReason($server->id, $result->getValue('reason'));
        
        $punishment = $this->punishPlayer(
	        $server,
	        $player,
	        $result->getValue('type'),
	        $result->getValue('extra'),
	        $result->getValue('punisher_id'),
	        $reason,
	        $result->getValue('details'),
	        $result->getValue('time')
        );

	    $punishment->load('reason')->makeVisible('reason');
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
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 * @throws ValidationException
	 */
	public function amnestyAction(Request $request, Response $response)
	{
		$validator = new Validator($this->getContainer('lang'));
		$validator
			->set('punishment_id', true, [
				new Number()
			]);

		$result = $validator->validate($this->getBody($request));
		if (!$result->getIsValid()) {
			throw new ValidationException($result->getFirstError(), 0);
		}

		$punishment = Punishment::where([
			'id' => $result->getValue('punishment_id'),
			'server_id' => $this->getServer($request)->id
		])->first();

		if (!$punishment) {
			throw new ValidationException('Punishment with id ' . $result->getValue('punishment_id') . ' not found!', 1);
		}

		$punishment->update([
			'status' => Punishment::STATUS_AMNESTIED,
		]);

		return $response->withStatus(200)->withJson([
			'success' => true,
			'punishment' => $punishment,
		]);
	}

	/**
	 * @param Server $server
	 * @param Player $player
	 * @param string $type
	 * @param int $extra
	 * @param int $punisherId
	 * @param Reason $reason
	 * @param string $details
	 * @param int $time
	 * @return Punishment
	 * @throws ValidationException
	 */
	protected function punishPlayer(Server $server, Player $player, $type, $extra, $punisherId, Reason $reason, $details, $time)
	{
		if ($punisherId > 0 && !Player::where('id', $punisherId)->exists()) {
			throw new ValidationException('Punisher with id ' . $punisherId . ' not found!', 2);
		}

		$punishment = Punishment::where([
			'player_id' => $player->id,
			'server_id' => $server->id,
			'type' => $type,
			'status' => Punishment::STATUS_PUNISHED
		])->first();

		$now = Carbon::now();
		$expired = $time > 0 ? Carbon::now()->addSeconds($time) : null;

		if ($punishment) {
			$punishment->update([
				'punisher_id' => $punisherId > 0 ? $punisherId : null,
				'ex tra' => $extra,
				'reason_id' => $reason->id,
				'details' => $details,
				'created_at' => $now,
				'updated_at' => $now,
				'expired_at' => $expired,
				'status' => Punishment::STATUS_PUNISHED
			]);
		} else {
			$punishment = new Punishment([
				'player_id' => $player->id,
				'punisher_id' => $punisherId > 0 ? $punisherId : null,
				'server_id' => $server->id,
				'type' => $type,
				'extra' => $extra,
				'reason_id' => $reason->id,
				'details' => $details,
                'created_at' => $now,
                'expired_at' => $expired,
				'status' => Punishment::STATUS_PUNISHED
			]);
			$punishment->save();
		}
		return $punishment;
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
