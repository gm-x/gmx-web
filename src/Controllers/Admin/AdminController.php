<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use GameX\Core\Cache\Cache;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Response;
use \GameX\Constants\Admin\AdminConstants;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Server;
use \GameX\Models\Player;
use \Carbon\Carbon;

class AdminController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return AdminConstants::ROUTE_INDEX;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, Response $response)
    {

        $today = Carbon::today();
//		TODO: For active menu checks
//		$request->getAttribute('route')->getName();
        return $this->getView()->render($response, 'admin/index.twig', [
            'servers' => [
                'total' => Server::count(),
                'active' => Server::where('active', 1)->count(),
            ],
            'users' => [
                'total' => UserModel::count(),
                'new' => UserModel::whereDate('created_at', $today)->count()
            ],
            'players' => [
                'total' => Player::count(),
                'new' => Player::whereDate('created_at', $today)->count()
            ],
        ]);
    }

    public function emulatorsAction(ServerRequestInterface $request, Response $response)
    {
	    $data = Player::groupBy('emulator')
		    ->selectRaw('emulator, count(*) as total')
		    ->pluck('total', 'emulator')->all();
	    return $response->withStatus(200)->withJson([
		    'success' => true,
		    'data' => $data,
	    ]);
    }

	public function onlineAction(ServerRequestInterface $request, Response $response)
	{
		/** @var Cache $cache */
		$cache = $this->container->get('cache');
		$data = $cache->get('chart_online');

	    return $response->withStatus(200)->withJson([
		    'success' => true,
		    'dates' => array_keys($data),
		    'online' => array_values($data),
	    ]);
	}
}
