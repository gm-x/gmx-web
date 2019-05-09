<?php

namespace GameX\Core\Assets;

class Manager
{
	protected $assets = [
		'pickadate' => [
			'scripts' => [
				'/assets/js/pickadate.min.js'
			],
			'styles' => [
				'/assets/css/pickadate.css'
			],
			'require' => [
				'jquery'
			]
		],
		'jquery' => [
			'scripts' => [
				'/assets/js/jquery-3.3.1.min.js'
			],
			'styles' => [],
			'require' => []
		]
	];
	protected $included = [];

	public function includeAsset($asset)
	{
		$this->checkDependencies($asset);
	}

	public function getIncludedAssetsStyles()
	{
		$styles = [];
		foreach ($this->included as $asset) {
			$styles = array_merge($styles, $this->assets[$asset]['styles']);
		}
		return $styles;
	}

	public function getIncludedAssetsScripts()
	{
		$scripts = [];
		foreach ($this->included as $asset) {
			$scripts = array_merge($scripts, $this->assets[$asset]['scripts']);
		}
		return $scripts;
	}

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