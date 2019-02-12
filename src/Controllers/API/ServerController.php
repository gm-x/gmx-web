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
    public function infoAction(Request $request, Response $response, array $args)
    {
        $server = $this->getServer($request);
        
        $groups = [];
        foreach ($server->groups as $group) {
            $groups[] = $group->id;
        }
        
        $privileges = Privilege::with('player')
            ->where('active', 1)
            ->whereIn('group_id', $groups)
            ->where(function ($query) {
                $query
                    ->whereNull('expired_at')
                    ->orWhere('expired_at','>=', Carbon::today()->toDateString());
            })
            ->get();
        
        $reasons = $server->reasons()->where('active', 1)->get();
        
        return $response->withStatus(200)->withJson([
            'success' => true,
            'server_id' => $server->id,
            'groups' => $server->groups,
            'privileges' => $privileges,
            'reasons' => $reasons,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return static
     * @throws ApiException
     */
    public function mapAction(Request $request, Response $response, array $args)
    {
        $validator = new Validator($this->getContainer('lang'));
        $validator->set('map', true)->set('max_players', true, [
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
        
        $server = $this->getServer($request);
        $server->map_id = $map->id;
        $server->num_players = 0;
        $server->max_players = $result->getValue('max_players');
        $server->save();
        
        Player::where('server_id', $server->id)->update(['server_id' => null]);
        
        return $response->withStatus(200)->withJson([
            'success' => true
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return static
     * @throws ApiException
     */
    public function updateAction(Request $request, Response $response, array $args)
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
