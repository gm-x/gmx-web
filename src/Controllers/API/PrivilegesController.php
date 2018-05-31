<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Group;

class PrivilegesController extends BaseApiController {
    public function indexAction(Request $request, Response $response, array $args) {
        $privileges = [];
        /** @var Group $group */
        foreach (Group::with('players')->where('server_id', '=', 1)->get() as $group) {
            foreach ($group->players as $player) {
                $privileges[] = [
                    'steamid' => $player->player->steamid,
                    'nick' => $player->player->nick,
                    'auth_type' => $player->player->auth_type,
                    'prefix' => $player->prefix ?: $group->title,
                ];
            }
        }

        return $response->withJson([
           'success' => true,
            'privileges' => $privileges
        ]);
    }
}
