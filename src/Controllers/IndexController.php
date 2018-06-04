<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use GameX\Models\Server;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
    	/** @var \Stash\Pool $item$cache */
		$cache = $this->getContainer('cache');

		$servers = [];
		/** @var Server $server */
		foreach (Server::all() as $server) {
			$item = $cache->getItem('server_' . $server->id);
			if ($item->isMiss()) {
				$servers[] = [
					'success' => false,
					'name' => $server->name,
					'ip' => $server->ip,
					'port' => $server->port,
				];
			} else {
				$data = $item->get();
				$servers[] = [
					'success' => true,
					'name' => $server->name,
					'ip' => $server->ip,
					'port' => $server->port,
					'map' => $data['map'],
					'players' => $data['players'],
					'maxPlayers' => $data['maxPlayers'],
				];
			}
		}

        return $this->render('index/index.twig', [
        	'servers' => $servers,
		]);
    }
}
