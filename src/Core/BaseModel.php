<?php

namespace GameX\Core;

use \Illuminate\Database\Eloquent\Model;
use \Psr\Container\ContainerInterface;
use \DateTimeInterface;
use \GameX\Core\Rememberable\Rememberable;
use \GameX\Models\Group;
use \GameX\Models\Hooks\GroupHook;
use \GameX\Models\Privilege;
use \GameX\Models\Hooks\PrivilegeHook;
use \GameX\Models\Reason;
use \GameX\Models\Hooks\ReasonsHook;

abstract class BaseModel extends Model
{
    
    use Rememberable;
    
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
     * @var bool
     */

    /**
     * On model boot event
     */
    public static function boot()
    {
        parent::boot();

        // If already booted more than one time skip next section
        if (count(self::$booted) > 1) {
            return;
        }

        // TODO: WTF ??? It's need for create connection
        self::$container['db'];

        Group::observe(new GroupHook());
        Privilege::observe(new PrivilegeHook());
        Reason::observe(new ReasonsHook());
    }
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->getTimestamp();
    }
    
    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}
