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
	 * @var string|null
	 */
	protected $icon;

	/**
	 * @param string $className
	 * @param array $config
	 * @param string|null $icon
	 */
	public function __construct($className, array $config = [], $icon = null)
	{
		$this->className = $className;
		$this->config = $config;
		$this->icon = $icon;
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

	/**
	 * @return string|null
	 */
	public function getIcon()
	{
		return $this->icon;
	}
}