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
     * On model boot event
     */
    public static function boot()
    {
        // TODO: WTF ??? It's need for create connection
        self::$container['db'];
        parent::boot();

        Group::observe(new GroupHook());
        Privilege::observe(new PrivilegeHook());
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
