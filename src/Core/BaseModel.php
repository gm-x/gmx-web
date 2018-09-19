<?php
namespace GameX\Core;

use \Illuminate\Database\Eloquent\Model;
use \Psr\Container\ContainerInterface;
use \DateTimeInterface;
use \GameX\Core\Rememberable\Rememberable;

abstract class BaseModel extends Model {

    use Rememberable;

    /**
     * @var bool
     */
    protected $rememberCache = true;

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
    
    protected function serializeDate(DateTimeInterface $date) {
        return $date->getTimestamp();
    }

	/**
	 * @param ContainerInterface $container
	 */
	public static function setContainer(ContainerInterface $container) {
		self::$container = $container;
	}

//    public function find($id, $columns = ['*']) {
//	    /** @var \GameX\Core\Log\Logger $logger */
//        $logger = self::$container->get('log');
//        $logger->debug('Find model ' . static::class . ' with id ' . $id);
//	    return parent::find($id, $columns);
//    }
//
//    public function findMany($ids, $columns = ['*']) {
//        /** @var \GameX\Core\Log\Logger $logger */
//        $logger = self::$container->get('log');
//        $logger->debug('Find model ' . static::class . ' with id ' . $ids);
//        return parent::findMany($ids, $columns);
//    }

//    protected function newBaseQueryBuilder() {
//        /** @var \GameX\Core\Log\Logger $logger */
//        $logger = self::$container->get('log');
//        $logger->debug('New Base Query Builder ' . static::class);
//
//	    return parent::newBaseQueryBuilder();
//    }
}
