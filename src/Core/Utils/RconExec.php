<?php

namespace GameX\Core\Utils;

use \Psr\Container\ContainerInterface;
use \xPaw\SourceQuery\SourceQuery;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;
use \GameX\Models\Server;
use \GameX\Core\Configuration\Exceptions\NotFoundException;
use \xPaw\SourceQuery\Exception\InvalidArgumentException;
use \xPaw\SourceQuery\Exception\InvalidPacketException;
use \xPaw\SourceQuery\Exception\SocketException;

class RconExec
{
	const ENGINES = [
		'cstrike' => SourceQuery::GOLDSOURCE,
		'csgo' => SourceQuery::SOURCE
	];

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * RconExec constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param Server $server
	 * @throws InvalidArgumentException
	 * @throws InvalidPacketException
	 * @throws NotFoundException
	 * @throws SocketException
	 */
	public function reloadAdmins(Server $server)
	{
        /** @var Config $config */
    	$config = $this->container->get('preferences');
	    if ($config->getNode('cron')->get('reload_admins')) {
		    JobHelper::createTaskIfNotExists('rcon_exec', [
			    'server_id' => $server->id,
			    'command' => 'amx_reloadadmins'
		    ], null, function (Task $task) use ($server) {
			    return isset($task->data['server_id']) && $task->data['server_id'] == $server->id;
		    });
	    } else {
	    	$this->sendCommand($server, 'amx_reloadadmins');
	    }
	}

	/**
	 * @param Server $server
	 * @param $command
	 * @throws InvalidArgumentException
	 * @throws InvalidPacketException
	 * @throws SocketException
	 */
	public function sendCommand(Server $server, $command)
	{
		if (!empty($server->rcon) && array_key_exists($server->type, self::ENGINES)) {
			$query = new SourceQuery();
			$query->Connect($server->ip, $server->port, 10, self::ENGINES[$server->type]);
			$query->SetRconPassword($server->rcon);
			$query->Rcon($command);
			$query->Disconnect();
		}
	}
}