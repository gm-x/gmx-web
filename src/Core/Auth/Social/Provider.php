<?php

namespace GameX\Core\Auth\Social;

class Provider
{
	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param string $className
	 * @param array $config
	 */
	public function __construct($className, array $config = [])
	{
		$this->className = $className;
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}
}