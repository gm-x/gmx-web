<?php

namespace GameX\Core\Cache;

use \Psr\Container\ContainerInterface;
use \Stash\Interfaces\DriverInterface;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Node;
use \Stash\Driver\Redis;
use \Stash\Driver\Memcache;
use \GameX\Core\Cache\Drivers\FileSystem;
use \GameX\Core\Configuration\Exceptions\NotFoundException;

class CacheDriverFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $path
     * @return DriverInterface
     * @throws NotFoundException
     */
    public static function getDriver(ContainerInterface $container, $path)
    {
        /** @var Config $config */
        $config = $container->get('config');
        $node = $config->getNode('cache');
        $driver = null;
        switch ($node->get('driver')) {
            case 'redis': {
                $driver = self::getRedisDriver($node->get('options'));
            } break;

            case 'memcached': {
                $driver = self::getMemcachedDriver($node->get('options'));
            } break;

            default: {
                $driver = self::getFileSystemDriver($container->get('root') . $path);
            }
        }
        return $driver;
    }

    /**
     * @param Node $config
     * @return Redis
     */
    protected static function getRedisDriver(Node $config)
    {
        $server = [
            'server' => (string) $config->get('host', 'localhost'),
            'port' => (int) $config->get('port', 6379),
        ];
        if ($config->exists('password')) {
            $server = (string) $config->get('password');
        }

        $options = [
            'servers' => [$server]
        ];

        if ($config->exists('db')) {
            $options['database'] = (string) $config->get('db');
        }

        return new Redis($options);
    }

    /**
     * @param Node $config
     * @return Memcache
     */
    protected static function getMemcachedDriver(Node $config)
    {
        $server = [
            (string) $config->get('host', 'localhost'),
            (int) $config->get('port', 6379),
        ];
        $options = [
            'servers' => [$server]
        ];

        $extension = $config->get('extension');
        if ($extension !== null && in_array($extension, ['memcache', 'memcached'], true)) {
            $options['extension'] = $extension;
        }

        return new Memcache($options);
    }

    /**
     * @param string $path
     * @return FileSystem
     */
    protected static function getFileSystemDriver($path)
    {
        return new FileSystem([
            'path' => $path,
            'encoder' => 'Serializer'
        ]);
    }
}