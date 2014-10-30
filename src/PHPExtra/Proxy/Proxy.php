<?php

namespace PHPExtra\Proxy;

use PHPExtra\EventManager\Event\CancellableEventInterface;
use PHPExtra\EventManager\EventManagerAwareInterface;
use PHPExtra\EventManager\EventManagerInterface;
use PHPExtra\Proxy\Engine\ProxyEngineInterface;
use PHPExtra\Proxy\Event\ProxyEngineEvent;
use PHPExtra\Proxy\Event\ProxyEventInterface;
use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyEngineListener;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Http\RequestInterface;
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
    const NAME = 'PHPExtra\Proxy';

    const VERSION = '1.0.0';

    /**
     * @var ProxyEngineInterface
     */
    private $engine;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

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
    private $isInitialized = false;

    /**
     * @return ProxyEngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param ProxyEngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(ProxyEngineInterface $engine)
    {
        $this->engine = $engine;

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

            $this->eventManager
                ->addListener(new ProxyEngineListener($this->engine))
                ->addListener(new DefaultProxyListener());

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
            // if response is present, all engines should skip it passing given response
            $response = $this->callProxyEvent(new ProxyEngineEvent($request, $response))->getResponse();
            // response is now ready - if present, for post processing
            $response = $this->callProxyEvent(new ProxyResponseEvent($request, $response))->getResponse();
        } catch (\Exception $e) {
            $response = $this->callProxyEvent(new ProxyExceptionEvent($e, $request));
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
            throw $this->createProxyException('Proxy cancelled your request');
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
