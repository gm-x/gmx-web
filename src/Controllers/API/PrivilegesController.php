<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Group;
use \GameX\Models\Privilege;

class PrivilegesController extends BaseApiController {
    public function indexAction(Request $request, Response $response, array $args) {
        $privileges = [];
        /** @var Group $group */
        $groups = Group::with('players')->where('server_id', '=', $request->getAttribute('server_id'))->get();
        foreach ($groups as $group) {
        	/** @var Privilege $privilege */
			foreach ($group->players as $privilege) {
				$player = $privilege->player;
                $privileges[] = [
                    'steamid' => $player->steamid,
                    'nick' => $player->nick,
                    'auth_type' => $player->auth_type,
					'password' => $player->password,
                    'prefix' => $privilege->prefix ?: $group->title,
					'group' => $group->id,
					'flags' => $group->flags,
					'expired' => $privilege->expired()->getTimestamp(),
                ];
            }
        }

        return $response->withJson([
           'success' => true,
            'privileges' => $privileges,
        ]);
    }
}
