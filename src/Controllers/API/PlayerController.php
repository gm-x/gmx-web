<?php

namespace GameX\Controllers\API;

use \Carbon\Carbon;
use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Punishment;
use \GameX\Models\Player;
use \GameX\Models\PlayerSession;
use \GameX\Models\PlayerPreference;
use \GameX\Models\Group;
use \GameX\Models\Access;
use \GameX\Models\Privilege;
use \Illuminate\Database\Eloquent\Builder;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\SteamID;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Validate\Rules\ArrayRule;
use \GameX\Core\Exceptions\ValidationException;

class PlayerController extends BaseApiController
{
    
    /**
     * @OA\Post(
     *     path="/api/player/connect",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="emulator",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="steamid",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="nick",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="ip",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="session_id",
     *                     type="integer"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Player connect response")
     * )
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
            throw new ValidationException($result->getFirstError(), 0);
        }
        
        $server = $this->getServer($request);
        $session = null;
	    $now = Carbon::now();
        
        /** @var Player|null $player */
        $player = Player::query()->when($result->getValue('id'), function (Builder $query) use ($result) {
                $query->where('id', $result->getValue('id'));
            })->orWhere(function (Builder $query) use ($result) {
                $query->whereIn('auth_type', [
                        Player::AUTH_TYPE_STEAM,
                        Player::AUTH_TYPE_STEAM_AND_PASS,
                        Player::AUTH_TYPE_STEAM_AND_HASH,
                    ])
	                ->where(function (Builder $query) use ($result) {
		                $query
			                ->where('emulator', $result->getValue('emulator'))
			                ->orWhere('emulator', 0);
	                })
	                ->where('steamid', $result->getValue('steamid'));
            })->orWhere(function (Builder $query) use ($result) {
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
	        $player->save();
        } else if($result->getValue('session_id')) {
            $session = PlayerSession::find($result->getValue('session_id'));
        } else {
            $session = $player->getActiveSession();
        }

        if ($player->exists) {
        	$save = false;
        	if (
		        $player->nick !== $result->getValue('nick') &&
		        !$player->hasAccess(Player::ACCESS_BLOCK_CHANGE_NICK)
	        ) {
		        $player->nick = $result->getValue('nick');
		        $save = true;
	        }

        	if ($player->emulator === 0) {
		        $player->emulator = $result->getValue('emulator');
		        $player->ip = $result->getValue('ip');
		        $save = true;
	        }

	        if ($save) {
		        $player->save();
	        }
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
        }
        
        $session->save();

        /** @var Punishment[] $punishments */
        $punishments = $player->getActivePunishments($server);

        /** @var PlayerPreference $preferences */
        $preferences = $player->preferences()
	        ->where('server_id', $server->id)
	        ->first();

	    $access = $player->privileges()->with('group')->get()->map(function (Privilege $privilege) {
		    return $privilege->group ? $privilege->group->access : [];
	    })->flatten()->map(function (Access $access) {
		    return $access->key;
	    })->unique()->values()->all();

	    $immunity = $player->privileges->map(function (Privilege $privilege) {
	        return $privilege->group->immunity;
        })->max();
    
        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');
        $cache->clear('players_online', $server);
        
        return $this->response($response, 200, [
            'success' => true,
            'player_id' => $player->id,
            'session_id' => $session->id,
            'user_id' => $player->user ? $player->user->id : null,
            'punishments' => $punishments,
	        'preferences' => $preferences ? $preferences->data : new \stdClass(),
	        'access' => $access,
            'immunity' => $immunity,
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/api/player/disconnect",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="session_id",
     *                     type="integer"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Player disconnect response")
     * )
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
            throw new ValidationException($result->getFirstError(), 0);
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
     * @OA\Post(
     *     path="/api/player/assign",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="token",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Player assign response")
     * )
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
            throw new ValidationException($result->getFirstError(), 0);
        }

        $player = Player::find($result->getValue('id'), ['id', 'user_id']);
        if (!$player) {
            throw new ValidationException('Player not found', 1);
        }
        if ($player->user_id !== null) {
            throw new ValidationException('User already assigned', 2);
        }
        $user = UserModel::where('token', $result->getValue('token'))->first(['id']);
        if (!$user) {
            throw new ValidationException('Invalid user token', 3);
        }
        
        $player->user_id = $user->id;
        $player->save();
        
        return $this->response($response, 200, [
            'success' => true,
            'user_id' => $user->id
        ]);
    }

	/**
	 * @OA\Post(
	 *     path="/api/player/preferences",
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 @OA\Property(
	 *                     property="player_id",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="data",
	 *                     type="object"
	 *                 )
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="200", description="Player preferences response")
	 * )
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 * @throws ValidationException
	 */
    public function preferencesAction(Request $request, Response $response)
    {
	    $validator = new Validator($this->getContainer('lang'));
	    $validator
		    ->set('player_id', true, [
		        new Number(1)
		    ])
		    ->set('data', true, [
			    new ArrayRule()
		    ], [
			    'check' => Validator::CHECK_IGNORE,
			    'trim' => false
		    ]);

	    $result = $validator->validate($this->getBody($request));
	    if (!$result->getIsValid()) {
		    throw new ValidationException($result->getFirstError(), 0);
	    }

	    $player = Player::find($result->getValue('player_id'));
	    if (!$player) {
		    throw new ValidationException('Player not found', 1);
	    }

	    $server = $this->getServer($request);

	    /** @var PlayerPreference $preferences */
	    $preferences = PlayerPreference::firstOrNew([
		    'server_id' => $server->id,
		    'player_id' => $player->id,
	    ]);

	    $preferences->data = array_merge($preferences->data ?: [], $result->getValue('data'));
	    $preferences->save();

	    return $this->response($response, 200, [
		    'success' => true,
	    ]);
    }

	/**
	 * @OA\Post(
	 *     path="/api/player/privilege/add",
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 @OA\Property(
	 *                     property="player_id",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="group_id",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="expired_at",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="active",
	 *                     type="boolean"
	 *                 )
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="200", description="Player preferences response")
	 * )
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 * @throws ValidationException
	 */
	public function privilegeAddAction(Request $request, Response $response)
	{
		$validator = new Validator($this->getContainer('lang'));
		$validator
			->set('player_id', true, [
				new Number(1)
			])
			->set('group_id', true, [
				new Number(1)
			])
			->set('time', true, [
				new Number(0)
			])
            ->set('extend', false, [
                new Boolean()
            ]);

		$result = $validator->validate($this->getBody($request));
		if (!$result->getIsValid()) {
			throw new ValidationException($result->getFirstError(), 0);
		}
		$player = Player::find($result->getValue('player_id'));
		if (!$player) {
			throw new ValidationException('Player not found', 1);
		}

		$group = Group::find($result->getValue('group_id'));
		if (!$group || $group->server_id !== $this->getServer($request)->id) {
			throw new ValidationException('Group not found', 2);
		}

		$expiredAt = $result->getValue('time') > 0
			? Carbon::now()->addSeconds($result->getValue('time'))
			: null;

		$privilege = Privilege::where(['player_id' => $player->id, 'group_id' => $group->id])->first();
		if (!$privilege) {
            $privilege = new Privilege([
                'player_id' => $player->id,
                'group_id' => $group->id,
                'expired_at' => $expiredAt,
                'active' => true,
            ]);
        } else if ($result->getValue('extend')) {
		    if ($privilege->expired_at !== null) {
                $privilege->expired_at = $result->getValue('time') > 0
                    ? $privilege->expired_at->addSeconds($result->getValue('time'))
                    : null;
                $privilege->active = true;
            }
		} elseif ($privilege->expired_at === null || ($expiredAt !== null && $expiredAt->isBefore($privilege->expired_at))) {
			throw new ValidationException('Privilege already exists');
		} else {
			$privilege->expired_at = $expiredAt;
			$privilege->active = true;
		}

		$privilege->save();

		return $this->response($response, 200, [
			'success' => true,
			'privilege' => $privilege,
			'group' => $group
		]);
	}
}
