<?php

namespace PHPExtra\Proxy;

use PHPExtra\EventManager\Event\CancellableEventInterface;
use PHPExtra\EventManager\EventManager;
use PHPExtra\EventManager\EventManagerAwareInterface;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\EventManager\Exception\RuntimeException;
use PHPExtra\Proxy\Cache\CacheManagerInterface;
use PHPExtra\Proxy\Cache\DefaultCacheManager;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Event\ProxyAdapterEvent;
use PHPExtra\Proxy\Event\ProxyEventInterface;
use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\EventListener\ProxyAdapterListener;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Exception\CancelledEventException;
use PHPExtra\Proxy\Firewall\DefaultFirewall;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Http\RequestInterface;
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
    const NAME = 'phpextra/proxy';

    const VERSION = '1.0.0';

    const URL = 'https://packagist.org/packages/phpextra/proxy';

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
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @param bool $debug
     */
    function __construct($debug = false)
    {
        $this->debug = $debug;
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
    public function setCacheManager($cacheManager)
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
     * Initialize listeners
     */
    private function init()
    {
        if (!$this->isInitialized) {

            if (!$this->logger) {
                $this->logger = new NullLogger();
            }

            if (!$this->firewall) {
                $this->firewall = new DefaultFirewall();
            }

            if(!$this->cacheManager){
                $this->cacheManager = new DefaultCacheManager(new FilesystemStorage());
            }

            if(!$this->eventManager){
                $this->eventManager = new EventManager();
            }

            $this->eventManager
                ->setLogger($this->logger)
                ->addListener(new ProxyAdapterListener($this->adapter))
                ->addListener(new DefaultProxyListener($this->firewall))
                ->addListener(new ProxyCacheListener($this->cacheManager))
            ;

            foreach ($this->listeners as $listener) {
                $this->eventManager->addListener($listener[0], $listener[1]);
            }

            $this->isInitialized = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request)
    {
        $this->init();

        try {
            $response = $this->callProxyEvent(new ProxyRequestEvent($request))->getResponse();
            // if response is present, all adapters should skip it passing given response
            $response = $this->callProxyEvent(new ProxyAdapterEvent($request, $response))->getResponse();
            // response is now ready - if present, for post processing
            $response = $this->callProxyEvent(new ProxyResponseEvent($request, $response))->getResponse();
        } catch (\Exception $e) {
            if($this->debug === true){
                throw $e;
            }else{
                $response = $this->callProxyEvent(new ProxyExceptionEvent($e, $request))->getResponse();

                if($response === null){
                    throw new RuntimeException('Proxy failed due to an exception; it was also unable to generate error response', 1, $e);
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

        if ($event instanceof ProxyResponseEvent && !$event->hasResponse()) {
            throw $this->createProxyException('Proxy was unable to complete your request (empty response)');
        }

        return $event;
    }

    /**
     * Create exception with given message
     *
     * @param string $message
     *
     * @return \RuntimeException
     */
    private function createProxyException($message)
    {
        return new \RuntimeException($message);
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
