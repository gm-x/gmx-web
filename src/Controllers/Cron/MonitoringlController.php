<?php
namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Core\Jobs\JobResult;
use \GameX\Models\Task;
use \GameX\Models\Server;
use \GameX\Core\ServerQuery\ValveServerQuery;
use \GameX\Core\ServerQuery\ValveServerQueryException;

class MonitoringlController extends BaseCronController {
    public function run(Task $task) {

		/** @var \Stash\Pool $item$cache */
		$cache = self::getContainer('cache');

    	/** @var Server $server */
		foreach (Server::all() as $server) {
			try {
				$serverQuery = new ValveServerQuery($server->ip, $server->port, [
					'timeout' => 10,
					'engine' => ValveServerQuery::ENGINE_GOLDSRC,
				]);
				$data = $serverQuery->getInfo();

				$item = $cache->getItem('server_' . $server->id);
				$item->set($data);
				$cache->save($item);
			} catch (ValveServerQueryException $e) {
				//
			}
		}

		return new JobResult(true, 5);
    }
}
