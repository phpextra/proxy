<?php

namespace PHPExtra\Proxy;

use GuzzleHttp\Client;
use PHPExtra\EventManager\EventManager;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\Proxy\Adapter\Guzzle4\Guzzle4Adapter;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Cache\CacheStrategyInterface;
use PHPExtra\Proxy\Cache\DefaultCacheStrategy;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\Firewall\DefaultFirewall;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Logger\LoggerProxy;
use PHPExtra\Proxy\Storage\FilesystemStorage;
use PHPExtra\Proxy\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\NullLogger;

/**
 * The ProxyFactory class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyFactory implements ProxyFactoryInterface
{
    /**
     * @var ProxyAdapterInterface
     */
    protected $adapter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var CacheStrategyInterface
     */
    protected $cacheStrategy;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var FirewallInterface
     */
    protected $firewall;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @return ProxyFactory
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $proxy = new Proxy();

        $proxy
            ->setConfig($this->getConfig())
            ->setLogger($this->getLogger())
            ->setAdapter($this->getAdapter())
            ->setFirewall($this->getFirewall())
            ->setEventManager($this->getEventManager())
            ->setCacheStrategy($this->getCacheStrategy())
        ;

        return $proxy;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        if(!$this->config){
            $this->config = new Config();
        }
        return $this->config;
    }

    /**
     * @return ProxyAdapterInterface
     */
    public function getAdapter()
    {
        if(!$this->adapter){
            $client = new Client();
            $this->adapter = new Guzzle4Adapter($client);
            $this->adapter->setLogger($this->getLogger());
        }
        return $this->adapter;
    }

    /**
     * @param ProxyAdapterInterface $adapter
     *
     * @return $this
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return CacheStrategyInterface
     */
    public function getCacheStrategy()
    {
        if(!$this->cacheStrategy){
            $this->cacheStrategy = new DefaultCacheStrategy();
        }
        return $this->cacheStrategy;
    }

    /**
     * @param CacheStrategyInterface $cacheStrategy
     *
     * @return $this
     */
    public function setCacheStrategy($cacheStrategy)
    {
        $this->cacheStrategy = $cacheStrategy;

        return $this;
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if(!$this->eventManager){
            $this->eventManager = new EventManager();
            $this->eventManager->setLogger($this->getLogger());

            $this->eventManager
                ->addListener(new DefaultProxyListener($this->getFirewall()))
                ->addListener(new ProxyCacheListener($this->getCacheStrategy(), $this->getStorage(), 3600))
            ;
        }
        return $this->eventManager;
    }

    /**
     * @param EventManagerInterface $eventManager
     *
     * @return $this
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if(!$this->logger){
            $this->logger = new LoggerProxy(new NullLogger());
        }
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        if(!$this->storage){
            $this->storage = new FilesystemStorage();
        }
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     *
     * @return $this
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return FirewallInterface
     */
    public function getFirewall()
    {
        if(!$this->firewall){
            $this->firewall = new DefaultFirewall();
            $this->firewall->setLogger($this->getLogger());
        }
        return $this->firewall;
    }

    /**
     * @param FirewallInterface $firewall
     *
     * @return $this
     */
    public function setFirewall(FirewallInterface $firewall)
    {
        $this->firewall = $firewall;

        return $this;
    }

}