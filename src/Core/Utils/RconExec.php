<?php

namespace GameX\Core\Utils;

use \Psr\Container\ContainerInterface;
use \xPaw\SourceQuery\SourceQuery;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Log\Logger;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Models\Task;
use \GameX\Models\Server;
use \GameX\Core\Configuration\Exceptions\NotFoundException;
use \xPaw\SourceQuery\Exception\SourceQueryException;

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
	 * @param $command
	 */
	public function sendCommand(Server $server, $command)
	{
		if (!empty($server->rcon) && array_key_exists($server->type, self::ENGINES)) {
			try {
				$query = new SourceQuery();
				$query->Connect($server->ip, $server->port, 10, self::ENGINES[$server->type]);
				$query->SetRconPassword($server->rcon);
				$query->Rcon($command);
				$query->Disconnect();
			} catch (SourceQueryException $e) {
				$this->getLogger()->exception($e);
			}
		} else {
			$this->getLogger()->error(sprintf(
				'Can\'t execute rcon command %s for %s',
				$command, $server->name
			));
		}
	}

	/**
	 * @return Config
	 */
	protected function getPreferences()
	{
		return $this->container->get('preferences');
	}

	/**
	 * @return Logger
	 */
	protected function getLogger()
	{
		return $this->container->get('log');
	}
}