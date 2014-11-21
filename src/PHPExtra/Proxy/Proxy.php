<?php

namespace PHPExtra\Proxy;

use PHPExtra\EventManager\Event\CancellableEventInterface;
use PHPExtra\EventManager\EventManagerAwareInterface;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\Proxy\Cache\CacheStrategyInterface;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Event\ProxyEventInterface;
use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Exception\CancelledEventException;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

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
     * @var CacheStrategyInterface
     */
    private $cacheStrategy;

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
            $proxyRequestEvent = new ProxyRequestEvent($request, null, $this);
            $this->callProxyEvent($proxyRequestEvent);

            $request = $proxyRequestEvent->getRequest();
            $response = $proxyRequestEvent->getResponse();

            if(!$response){
                $response = $this->adapter->handle($request);
            }

            $proxyResponseEvent = new ProxyResponseEvent($request, $response, $this);
            $this->callProxyEvent($proxyResponseEvent);

            $response = $proxyResponseEvent->getResponse();

        } catch (\Exception $e) {
            if($this->debug == true){
                throw $e;
            }else{
                $proxyExceptionEvent = new ProxyExceptionEvent($e, $request, null, $this);
                $this->callProxyEvent($proxyExceptionEvent);
                $response = $proxyExceptionEvent->getResponse();

                if(!$response){
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
