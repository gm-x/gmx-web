<?php


namespace GameX\Core\Auth\Social;


class Provider
{
	protected $className;
	protected $config;
	protected $icon;

	public function __construct($className, array $config = [], $icon = null)
	{
		$this->className = $className;
		$this->config = $config;
		$this->icon = $icon;
	}

	public function getClassName()
	{
		return $this->className;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getIcon()
	{
		return $this->icon;
	}
}