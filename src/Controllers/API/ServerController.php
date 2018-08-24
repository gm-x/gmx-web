<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Privilege;

class ServerController extends BaseApiController {

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function indexAction(Request $request, Response $response, array $args) {
        $server = $this->getServer($request);
        
        foreach ($server->groups as $group) {
            $groups[] = $group->id;
        }
        
        $privileges = Privilege::with('player')
            ->where('active', 1)
            ->whereIn('group_id', $groups)
            ->where('expired_at', '>=', Carbon::today()->toDateString())
            ->get();
        
        $reasons = $server->reasons()->where('active', 1)->get();

        return $response->withJson([
			'success' => true,
            'server_id' => $server->id,
            'groups' => $server->groups,
            'privileges' => $privileges,
            'reasons' => $reasons,
        ]);
    }
}
