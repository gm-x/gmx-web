<?php
/**
 * @OA\Info(title="My First API", version="0.1")
 */
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Privilege;
use \GameX\Models\Map;
use \GameX\Models\Player;
use \GameX\Models\PlayerSession;
use \GameX\Models\ServerCommand;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\ArrayRule;
use \GameX\Core\Validate\Rules\ArrayCallback;
use \GameX\Core\Exceptions\ValidationException;

class ServerController extends BaseApiController
{
    
    /**
     * @OA\Post(
     *     path="/api/server/privileges",
     *     @OA\Response(response="200", description="Server privileges response")
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function privilegesAction(Request $request, Response $response)
    {
        $server = $this->getServer($request);
    
        $groups = [];
        foreach ($server->groups as $group) {
            $groups[] = $group->id;
        }
    
        $list = Privilege::with('player')
            ->where('active', 1)
            ->whereIn('group_id', $groups)
            ->where(function ($query) {
                $query
                    ->whereNull('expired_at')
                    ->orWhere('expired_at','>=', Carbon::today()->toDateString());
            })
            ->get();
        
        $privileges = [];
        /** @var Privilege $privilege */
        foreach ($list as $privilege) {
            /** @var Player $player */
            $player = $privilege->player;
            if (!$player) {
                continue;
            }

            if (!array_key_exists($player->id, $privileges)) {
                $privileges[$player->id] = $player->toArray();
                $privileges[$player->id]['privileges'] = [];
            }
    
            $privilege = $privilege->toArray();
            unset($privilege['player']);
            $privileges[$player->id]['privileges'][] = $privilege;
        }
    
        return $response->withStatus(200)->withJson([
            'success' => true,
            'groups' => $server->groups,
            'privileges' => array_values($privileges),
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/api/server/info",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="map",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="max_players",
     *                     type="integer"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Server info response")
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function infoAction(Request $request, Response $response)
    {
        $server = $this->getServer($request);

        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('map', true)
            ->set('max_players', true, [
                new Number(0),
            ]);

        $result = $validator->validate($this->getBody($request));

        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }

        $map = Map::firstOrCreate([
            'name' => $result->getValue('map'),
        ], [
            'map' => $result->getValue('map'),
        ]);

        $server->map_id = $map->id;
        $server->num_players = 0;
        $server->max_players = $result->getValue('max_players');
	    $server->ping_at = Carbon::now()->toDateTimeString();
        $server->save();

        /** @var \GameX\Core\Cache\Cache $cache */
        $cache = $this->getContainer('cache');
        $cache->clear('players_online', $server);
        
        return $response->withStatus(200)->withJson([
            'success' => true,
            'server_id' => $server->id,
            'map' => $map,
	        'time' => time()
        ]);
    }

	/**
	 * @OA\Post(
	 *     path="/api/server/ping",
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 @OA\Property(
	 *                     property="num_players",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="sessions",
	 *                     type="array",
	 *                     @OA\Items(
	 *                          type="integer"
	 *                     )
	 *                 )
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="200", description="Server ping response")
	 * )
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 * @throws ValidationException
	 */
	public function pingAction(Request $request, Response $response)
	{
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('num_players', true, [
                new Number(0),
            ])
            ->set('sessions', true, [
                new ArrayRule(),
                new ArrayCallback(function ($key, $value) {
                    $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    return $value !== false ? $value : null;
                }, '')
            ], [
            	'check' => Validator::CHECK_IGNORE,
	            'trim' => false
            ]);

        $result = $validator->validate($this->getBody($request));

        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }

        $now = Carbon::now();

        $server = $this->getServer($request);
		$server->fill([
		    'num_players' => $result->getValue('num_players'),
		    'ping_at' => $now,
		]);
		$server->save();

		PlayerSession::
			where('server_id', $server->id)
			->where('status', PlayerSession::STATUS_ONLINE)
			->whereNotIn('id',  $result->getValue('sessions'))
			->update([
				'status' => PlayerSession::STATUS_OFFLINE,
				'disconnected_at' => Carbon::now(),
			]);

        PlayerSession::whereIn('id',  $result->getValue('sessions'))
            ->update([
                'ping_at' => $now,
            ]);

        $commands = ServerCommand::where([
            'server_id' => $server->id,
            'delivered' => false,
        ])->get();

        ServerCommand::where([
            'server_id' => $server->id,
            'delivered' => false,
        ])->update([
            'delivered' => true,
        ]);

		return $response->withStatus(200)->withJson([
			'success' => true,
            'commands' => $commands,
		]);
	}
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ValidationException
     */
    public function updateAction(Request $request, Response $response)
    {
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('num_players', true, [
                new Number(0)
            ])->set('max_players', true, [
                new Number(0),
            ]);
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ValidationException($result->getFirstError());
        }
        
        $server = $this->getServer($request);
        $server->num_players = $result->getValue('num_players');
        $server->max_players = $result->getValue('max_players');
        $server->save();
        
        return $response->withStatus(200)->withJson([
            'success' => true
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/server/access",
     *     @OA\Response(response="200", description="Server access response")
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function accessAction(Request $request, Response $response)
    {
        $server = $this->getServer($request);

        return $response->withStatus(200)->withJson([
            'success' => true,
            'list' => $server->access,
        ]);
    }
}
