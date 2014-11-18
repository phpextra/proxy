<?php

namespace PHPExtra\Proxy;

use GuzzleHttp\Client;
use PHPExtra\EventManager\EventManager;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\Proxy\Adapter\Guzzle4\Guzzle4Adapter;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Cache\CacheManagerInterface;
use PHPExtra\Proxy\Cache\DefaultCacheManager;
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
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var FirewallInterface
     */
    protected $firewall;

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
            ->setLogger($this->getLogger())
            ->setAdapter($this->getAdapter())
            ->setFirewall($this->getFirewall())
            ->setEventManager($this->getEventManager())
            ->setCacheManager($this->getCacheManager())
        ;

        return $proxy;
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
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        if(!$this->cacheManager){
            $this->cacheManager = new DefaultCacheManager($this->getStorage());
        }
        return $this->cacheManager;
    }

    /**
     * @param CacheManagerInterface $cacheManager
     *
     * @return $this
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;

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
                ->addListener(new ProxyCacheListener($this->getCacheManager()))
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