<?php

namespace GameX\Core\Assets;

class Manager
{
	/**
	 * @var array
	 */
	protected $assets;

	/**
	 * @var array
	 */
	protected $included = [];

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * Manager constructor.
	 * @param Loader $loader
	 */
	public function __construct(Loader $loader)
	{
		$this->assets = $loader->load();
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setData($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * @param string $asset
	 */
	public function includeAsset($asset)
	{
		$this->checkDependencies($asset);
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return array
	 */
	public function getIncludedAssetsStyles()
	{
		$styles = [];
		foreach ($this->included as $asset) {
			$styles = array_merge($styles, $this->assets[$asset]['styles']);
		}
		return $styles;
	}

	/**
	 * @return array
	 */
	public function getIncludedAssetsScripts()
	{
		$scripts = [];
		foreach ($this->included as $asset) {
			$scripts = array_merge($scripts, $this->assets[$asset]['scripts']);
		}
		return $scripts;
	}

	/**
	 * @param string $key
	 * @return array
	 */
	protected function checkDependencies($key)
	{
		if (!array_key_exists($key, $this->assets)) {
			return [];
		}
		foreach ($this->assets[$key]['require'] as $asset) {
			$this->checkDependencies($asset);
			if (!in_array($asset, $this->included, true)) {
				$this->included[] = $asset;
			}
		}
		if (!in_array($key, $this->included, true)) {
			$this->included[] = $key;
		}
	}
}