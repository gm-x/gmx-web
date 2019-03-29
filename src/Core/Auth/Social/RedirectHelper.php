<?php

namespace GameX\Core\Auth\Social;

class RedirectHelper
{
	/**
	 * @var string|null
	 */
	protected static $url = null;

	/**
	 * @param string $url
	 */
	public static function redirect($url)
	{
		static::$url = $url;
	}

	/**
	 * @return bool
	 */
	public static function isRedirected()
	{
		return static::$url !== null;
	}

	/**
	 * @return string|null
	 */
	public static function getUrl()
	{
		return static::$url;
	}
}