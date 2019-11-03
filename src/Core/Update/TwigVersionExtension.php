<?php
namespace GameX\Core\Update;

use \Twig_Extension;
use \Twig_SimpleFunction;

class TwigVersionExtension extends Twig_Extension
{
	/**
	 * @var Manifest
	 */
	protected $manifest;

	public function __construct(Manifest $manifest)
	{
		$this->manifest = $manifest;
	}

	/**
	 * @return array
	 */
	public function getFunctions()
	{
		return [
			new Twig_SimpleFunction('version', [$this, 'version']),
		];
	}

	public function version()
	{
		return $this->manifest->getVersion();
	}
}