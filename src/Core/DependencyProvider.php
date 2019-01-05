<?php

namespace GameX\Core;

use \Pimple\ServiceProviderInterface;
use \Pimple\Container;
use \Psr\Container\ContainerInterface;

use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Providers\JsonProvider;
use \GameX\Core\Configuration\Providers\DatabaseProvider;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\NotFoundException;

use \GameX\Core\Session\Session;

use \Stash\Driver\FileSystem;
use \GameX\Core\Cache\Cache;
use \GameX\Core\Cache\Items\Preferences;
use \GameX\Core\Cache\Items\Permissions as PermissionsCache;

use \GameX\Core\Log\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\RotatingFileHandler;

use \Illuminate\Database\Capsule\Manager as DataBaseManager;

use \GameX\Core\Lang\Loaders\JSONLoader;
use \GameX\Core\Lang\Providers\SlimProvider;
use \GameX\Core\Lang\Language;
use \GameX\Core\Lang\Exceptions\BadLanguageException;
use \GameX\Core\Lang\Exceptions\CantReadException;

use \GameX\Core\Auth\Permissions;

use \GameX\Core\Auth\SentinelBootstrapper;
use \Cartalyst\Sentinel\Sentinel;

use \Slim\Views\Twig;
use \Slim\Views\TwigExtension;
use \GameX\Core\CSRF\Extension as CSRFExtension;
use \GameX\Core\Auth\ViewExtension as AuthViewExtension;
use \GameX\Core\Lang\Extension\ViewExtension as LangViewExtension;
use \GameX\Core\AccessFlags\ViewExtension as AccessFlagsViewExtension;
use \GameX\Core\Upload\ViewExtension as UploadFlagsViewExtension;
use \GameX\Core\Constants\ViewExtension as ConstantsViewExtension;

use \GameX\Core\Mail\Helpers\MailHelper;

use \GameX\Core\CSRF\Token;

use \GameX\Core\Upload\Upload;

use \GameX\Core\Update\Updater;
use \GameX\Core\Update\Manifest;

class DependencyProvider implements ServiceProviderInterface
{
    
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['base_url'] = function (ContainerInterface $container) {
            /** @var \Slim\Http\Uri $uri */
            $uri = $container->get('request')->getUri();
            return rtrim(str_ireplace('index.php', '', $uri->getBasePath()), '/');
        };
        
        $container['config'] = function (ContainerInterface $container) {
            return $this->getConfig($container);
        };
        
        $container['preferences'] = function (ContainerInterface $container) {
            return $this->getPreferences($container);
        };
        
        $container['session'] = function () {
            return $this->getSession();
        };
        
        $container['cache'] = function (ContainerInterface $container) {
            return $this->getCache($container);
        };
        
        $container['log'] = function (ContainerInterface $container) {
            return $this->getLogger($container);
        };
        
        $container['db'] = function (ContainerInterface $container) {
            return $this->getDataBase($container);
        };
        
        $container['lang'] = function (ContainerInterface $container) {
            return $this->getLanguage($container);
        };
        
        $container['permissions'] = function (ContainerInterface $container) {
            return $this->getPermissions($container);
        };
        
        $container['auth'] = function (ContainerInterface $container) {
            return $this->getAuth($container);
        };
        
        $container['view'] = function (ContainerInterface $container) {
            return $this->getView($container);
        };
        
        $container['mail'] = function (ContainerInterface $container) {
            return $this->getMail($container);
        };
        
        $container['csrf'] = function (ContainerInterface $container) {
            return $this->getCSRF($container);
        };
        
        $container['flash'] = function (ContainerInterface $container) {
            return $this->getFlashMessages($container);
        };
        
        $container['upload'] = function (ContainerInterface $container) {
            return $this->getUpload($container);
        };
        
        $container['updater'] = function (ContainerInterface $container) {
            return $this->getUpdater($container);
        };
        
        $container['modules'] = function (ContainerInterface $container) {
            $modules = new \GameX\Core\Module\Module();
            //	$modules->addModule(new \GameX\Modules\TestModule\Module());
            return $modules;
        };
    }
    
    /**
     * @param ContainerInterface $container
     * @return Config
     * @throws CantLoadException
     */
    public function getConfig(ContainerInterface $container)
    {
        $provider = new JsonProvider($container->get('root') . '/config.json');
        return new Config($provider);
    }
    
    /**
     * @param ContainerInterface $container
     * @return Config
     * @throws CantLoadException
     */
    public function getPreferences(ContainerInterface $container)
    {
        $provider = new DatabaseProvider($container->get('cache'));
        return new Config($provider);
    }
    
    /**
     * @return Session
     */
    public function getSession()
    {
        return new Session();
    }
    
    /**
     * @param ContainerInterface $container
     * @return Cache
     */
    public function getCache(ContainerInterface $container)
    {
        $driver = new FileSystem([
            'path' => $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
            'encoder' => 'Serializer'
        ]);
        $cache = new Cache($driver);
        $cache->add('preferences', new Preferences());
        $cache->add('permissions', new PermissionsCache());
        return $cache;
    }
    
    /**
     * @param ContainerInterface $container
     * @return Logger
     */
    public function getLogger(ContainerInterface $container)
    {
        $formatter = new LineFormatter(null, null, true, true);
        
        $logPath = $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'log.log';
        $handler = new RotatingFileHandler($logPath, 10, Logger::DEBUG);
        $handler->setFormatter($formatter);
        
        
        $logger = new Logger('gmx');
        $logger->pushHandler($handler);
        return $logger;
    }
    
    /**
     * @param ContainerInterface $container
     * @return DataBaseManager
     * @throws NotFoundException
     */
    public function getDataBase(ContainerInterface $container)
    {
        /** @var Config $config */
        $config = $container->get('config');
        
        $capsule = new DataBaseManager;
        $capsule->addConnection($config->getNode('db')->toArray());
        
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        if ($config->getNode('log')->get('queries', false)) {
            $capsule->getConnection()->enableQueryLog();
        }
        
        return $capsule;
    }
    
    /**
     * @param ContainerInterface $container
     * @return Language
     * @throws BadLanguageException
     * @throws CantReadException
     * @throws NotFoundException
     */
    public function getLanguage(ContainerInterface $container)
    {
        /** @var Config $config */
        $config = $container->get('preferences');
        
        $loader = new JSONLoader($container['root'] . DIRECTORY_SEPARATOR . 'languages');
        $provider = new SlimProvider($container->get('request'));
        return new Language($loader, $provider, $config->getNode('languages')->toArray(),
            $config->getNode('main')->get('language'));
    }
    
    /**
     * @param ContainerInterface $container
     * @return Permissions
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function getPermissions(ContainerInterface $container)
    {
        return new Permissions($container);
    }
    
    /**
     * @param ContainerInterface $container
     * @return Sentinel
     */
    public function getAuth(ContainerInterface $container)
    {
        $container->get('db');
        $bootsrap = new SentinelBootstrapper($container->get('request'), $container->get('session'));
        return $bootsrap->createSentinel();
    }
    
    /**
     * @param ContainerInterface $container
     * @return Twig
     * @throws NotFoundException
     */
    public function getView(ContainerInterface $container)
    {
        /** @var Config $config */
        $config = $container->get('config');
        /** @var Config $preferences */
        $preferences = $container->get('preferences');
        
        $settings = $config->getNode('view')->toArray();
        $settings['cache'] = $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache';
        $theme = $preferences->getNode('main')->get('theme', 'default');
        
        $root = $container->get('root') . 'theme' . DIRECTORY_SEPARATOR;
        
        $paths = [
            $root . $theme . DIRECTORY_SEPARATOR . 'templates'
        ];
        
        // Fallback for custom theme haven't needed template
        if ($theme !== 'default') {
            $paths[] = $root . 'default' . DIRECTORY_SEPARATOR . 'templates';
        }
        
        $view = new Twig($paths, $settings);
        
        /** @var \Psr\Http\Message\UriInterface $uri */
        $uri = $container->get('request')->getUri();
        
        $view->addExtension(new TwigExtension($container->get('router'), $container->get('base_url')));
        $view->addExtension(new CSRFExtension($container->get('csrf')));
        $view->addExtension(new AuthViewExtension($container->get('auth'), $container->get('permissions')));
        $view->addExtension(new LangViewExtension($container->get('lang')));
        $view->addExtension(new AccessFlagsViewExtension());
        $view->addExtension(new Twig_Dump());
        $view->addExtension(new UploadFlagsViewExtension($container->get('upload')));
        $view->addExtension(new ConstantsViewExtension());
        
        $view->getEnvironment()->addGlobal('flash_messages', $container->get('flash'));
        $view->getEnvironment()->addGlobal('currentUri', (string)$uri->getPath());
        $view->getEnvironment()->addGlobal('title', $preferences->getNode('main')->get('title'));
        
        return $view;
    }
    
    /**
     * @param ContainerInterface $container
     * @return MailHelper
     * @throws NotFoundException
     */
    public function getMail(ContainerInterface $container)
    {
        /** @var Config $config */
        $config = $container->get('preferences');
        
        return new MailHelper($container->get('view'), $config->getNode('mail'));
    }
    
    /**
     * @param ContainerInterface $container
     * @return Token
     */
    public function getCSRF(ContainerInterface $container)
    {
        return new Token($container->get('session'));
    }
    
    /**
     * @param ContainerInterface $container
     * @return FlashMessages
     */
    public function getFlashMessages(ContainerInterface $container)
    {
        return new FlashMessages($container->get('session'), 'flash_messages');
    }
    
    public function getUpload(ContainerInterface $container)
    {
        return new Upload($container->get('root') . 'public/upload', $container->get('base_url') . '/upload');
    }
    
    public function getUpdater(ContainerInterface $container)
    {
        $manifest = new Manifest($container->get('root') . 'manifest.json');
        return new Updater($manifest);
    }
}
