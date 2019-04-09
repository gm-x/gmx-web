<?php

namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Privilege;
use \GameX\Models\Map;
use \GameX\Models\Player;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Exceptions\ApiException;

class ServerController extends BaseApiController
{
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
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
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function reasonsAction(Request $request, Response $response)
    {
        $server = $this->getServer($request);
        $reasons = $server->reasons()->where('active', 1)->get();

        return $response->withStatus(200)->withJson([
            'success' => true,
            'reasons' => $reasons,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
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
            throw new ApiException($result->getFirstError(), ApiException::ERROR_VALIDATION);
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
            'map' => $map
        ]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 * @throws ApiException
	 */
	public function pingAction(Request $request, Response $response)
	{
		$server = $this->getServer($request);
		$server->ping_at = Carbon::now()->toDateTimeString();
		$server->save();

		return $response->withStatus(200)->withJson([
			'success' => true
		]);
	}
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
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
            throw new ApiException($result->getFirstError(), ApiException::ERROR_VALIDATION);
        }
        
        $server = $this->getServer($request);
        $server->num_players = $result->getValue('num_players');
        $server->max_players = $result->getValue('max_players');
        $server->save();
        
        return $response->withStatus(200)->withJson([
            'success' => true
        ]);
    }
}
