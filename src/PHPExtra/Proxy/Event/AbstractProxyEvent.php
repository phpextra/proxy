<?php

namespace PHPExtra\Proxy\Event;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;
use PHPExtra\EventManager\Event\CancellableEvent;
use PHPExtra\EventManager\Event\CancellableEventInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * The AbstractProxyEvent class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
abstract class AbstractProxyEvent extends CancellableEvent implements ProxyEventInterface, LoggerAwareInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    function __construct(RequestInterface $request, ResponseInterface $response = null)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Tell if current event was handled by other listeners and has a response
     *
     * @return bool
     */
    public function hasResponse()
    {
        return $this->getResponse() !== null;
    }
}