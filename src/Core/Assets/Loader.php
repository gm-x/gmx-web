<?php


namespace GameX\Core\Assets;


class Loader
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function load()
	{
		return json_decode(file_get_contents($this->path), true);
	}
}