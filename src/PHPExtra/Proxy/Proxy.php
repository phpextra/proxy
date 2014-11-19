<?php

namespace PHPExtra\Proxy;

use PHPExtra\EventManager\Event\CancellableEventInterface;
use PHPExtra\EventManager\EventManager;
use PHPExtra\EventManager\EventManagerAwareInterface;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\Proxy\Cache\CacheManagerInterface;
use PHPExtra\Proxy\Cache\DefaultCacheManager;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Event\ProxyEventInterface;
use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Exception\CancelledEventException;
use PHPExtra\Proxy\Firewall\DefaultFirewall;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Logger\LoggerProxy;
use PHPExtra\Proxy\Storage\FilesystemStorage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The proxy
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Proxy implements ProxyInterface, EventManagerAwareInterface, LoggerAwareInterface
{
    /**
     * @var ProxyAdapterInterface
     */
    private $adapter;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var FirewallInterface
     */
    private $firewall;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $listeners = array();

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param bool $debug
     */
    function __construct($debug = false)
    {
        $this->debug = $debug;
        $this->config = new Config();
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     *
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;

        return $this;
    }

    /**
     * Get proxy configuration
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set proxy configuration
     *
     * @param ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set adapter that will be used to proxy requests
     *
     * @param ProxyAdapterInterface $adapter
     *
     * @return $this
     */
    public function setAdapter(ProxyAdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * Set cache manager that will be making decisions about what and when should be cached
     *
     * @param CacheManagerInterface $cacheManager
     *
     * @return $this
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        return $this;
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

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request)
    {
        try {
            $response = $this->callProxyEvent(new ProxyRequestEvent($request, null, $this))->getResponse();

            if(!$response){
                $response = $this->adapter->handle($request);
            }

            // response is now ready - if present, for post processing
            $response = $this->callProxyEvent(new ProxyResponseEvent($request, $response, $this))->getResponse();
        } catch (\Exception $e) {
            if($this->debug === true){
                throw $e;
            }else{
                $response = $this->callProxyEvent(new ProxyExceptionEvent($e, $request, null, $this))->getResponse();
                if($response === null){
                    throw $this->createProxyException('Proxy failed due to an exception; it was also unable to generate error response', $e);
                }
            }
        }

        return $response;
    }

    /**
     * Calls given proxy event
     * Throws an exception if event was cancelled
     *
     * @param ProxyEventInterface $event
     *
     * @return ProxyEventInterface
     */
    private function callProxyEvent(ProxyEventInterface $event)
    {
        if ($event instanceof LoggerAwareInterface) {
            $event->setLogger($this->logger);
        }

        $this->eventManager->trigger($event);

        if ($event instanceof CancellableEventInterface && $event->isCancelled()) {
            throw new CancelledEventException($event);
        }

        return $event;
    }

    /**
     * Create exception with given message
     *
     * @param string     $message
     * @param \Exception $previous
     *
     * @return \RuntimeException
     */
    private function createProxyException($message, \Exception $previous = null)
    {
        return new \RuntimeException($message, null, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function addListener(ProxyListenerInterface $listener, $priority = null)
    {
        $this->listeners[] = array($listener, $priority);

        return $this;
    }
}
