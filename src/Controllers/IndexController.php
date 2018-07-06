<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Models\Server;

class IndexController extends BaseMainController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'index';
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
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
