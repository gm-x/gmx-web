<?php
namespace GameX\Core;

use \Illuminate\Database\Eloquent\Model;
use \Psr\Container\ContainerInterface;

abstract class BaseModel extends Model {

	/**
	 * @var ContainerInterface
	 */
	protected static $container;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * On model boot event
	 */
	public static function boot() {
		// TODO: WTF ??? It's need for create connection
		self::$container['db'];
	}

	/**
	 * @param ContainerInterface $container
	 */
	public static function setContainer(ContainerInterface $container) {
		self::$container = $container;
	}
}
