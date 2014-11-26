<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\ConfigInterface;
use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\Exception\CancelledEventException;
use PHPExtra\Proxy\Exception\RemoteServerCommunicationErrorException;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\Response;
use PHPExtra\Proxy\ProxyInterface;
use Psr\Log\LogLevel;

/**
 * The DefaultProxyListener class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DefaultProxyListener implements ProxyListenerInterface
{
    /**
     * @var FirewallInterface
     */
    private $firewall;

    /**
     * @param FirewallInterface $firewall
     */
    function __construct(FirewallInterface $firewall)
    {
        $this->firewall = $firewall;
    }

    /**
     * Filter and mark incoming requests
     *
     * @priority high
     *
     * @param ProxyRequestEvent $event
     */
    public function onProxyRequest(ProxyRequestEvent $event)
    {
        if (!$event->isCancelled() && !$event->hasResponse()) {

            $response = null;
            $request = $event->getRequest();
            $config = $event->getProxy()->getConfig();

            if($this->isSelfRequest($request, $config)){
                $event->setIsCancelled();
                $event->getLogger()->warning(sprintf('Proxy server made a call to itself that cannot be handled, request will be cancelled'));
            }elseif(in_array($request->getHost(), $config->getHostsOnPort($request->getPort()))){
                // display proxy welcome page as it is a direct hit
                $response = new Response($this->getResource('home.html', $event->getProxy()), 200);
            }elseif(!$this->firewall->isAllowed($request)){
                $event->setIsCancelled();
                $event->getLogger()->debug(sprintf('Request was cancelled as it was not allowed by a firewall'));
            }

            if($response){
                $event->setResponse($response);
            }
        }
    }

    /**
     * Returns true if proxy detected a self-request
     *
     * @param RequestInterface $request
     * @param ConfigInterface  $config
     *
     * @return bool
     */
    private function isSelfRequest(RequestInterface $request, ConfigInterface $config)
    {
        $proxyUniqueHeaderName = 'PROXY-ID';
        $proxyUniqueHeaderValue = md5($config->getSecret() . $request->getFingerprint());

        if ($request->hasHeaderWithValue($proxyUniqueHeaderName, $proxyUniqueHeaderValue)) {
            return true;
        }

        $request->addHeader($proxyUniqueHeaderName, $proxyUniqueHeaderValue);
        return false;
    }

    /**
     * Tag outgoing responses
     *
     * @priority low
     *
     * @param ProxyResponseEvent $event
     */
    public function onProxyResponse(ProxyResponseEvent $event)
    {
        if ($event->hasResponse()) {

            $fullProxyName = sprintf('%s v.%s', $event->getProxy()->getConfig()->getProxyName(), $event->getProxy()->getConfig()->getProxyVersion());

            $event->getLogger()->debug('Signing response with proxy name and version');
            $response = $event->getResponse();
            $response->addHeader('X-Powered-By', $fullProxyName);
            $response->addHeader('X-Served-By', $event->getProxy()->getConfig()->getProxyName());
            $response->addHeader('X-Proxy-Version', $event->getProxy()->getConfig()->getProxyVersion());
        }
    }

    /**
     * Handle unhandled exceptions
     *
     * @priority lowest
     *
     * @param ProxyExceptionEvent $event
     */
    public function onProxyException(ProxyExceptionEvent $event)
    {
        if (!$event->isCancelled() && !$event->hasResponse()) {

            $exception = $event->getException();

            if($exception instanceof CancelledEventException){
                $response = new Response($this->getResource('403.html', $event->getProxy()), 403);
            } else if($exception instanceof RemoteServerCommunicationErrorException) {
                $response = new Response($this->getResource('502.html', $event->getProxy()), 502);

                $event->getLogger()->error(sprintf('Remote server could not be contacted. Requested URI: %s', $exception->getRequestedUri()));
            } else {
                $response = new Response($this->getResource('500.html', $event->getProxy()), 500);
                $event->getLogger()->error(sprintf('Proxy caught an exception (%s): %s',
                    get_class($event->getException()), $event->getException()->getMessage())
                );
            }

            $event->setResponse($response);
        }
    }

    /**
     * Monitor outcome of an event
     *
     * @priority monitor
     *
     * @param ProxyResponseEvent $event
     */
    public function onLateProxyResponse(ProxyResponseEvent $event)
    {
        if ($event->hasResponse()) {
            $params = array(
                $event->getRequest()->getUri(),
                $event->getResponse()->getLength(),
                $event->getResponse()->getStatusCode(),
            );

            if ($event->getResponse()->getStatusCode() != 200) {
                $level = LogLevel::WARNING;
                $params[4] = '';
            } else {
                $level = LogLevel::INFO;
                $params[4] = 'OK';
            }

            $event->getLogger()->log(
                $level,
                vsprintf('< %s %s byte(s) %s %s', $params),
                array('response_length' => $event->getResponse()->getLength())
            );
        } else {
            $event->getLogger()->warning('Proxy failed to produce valid response object; it also failed to handle the error that occurred');
        }
    }

    /**
     * @param string         $name
     * @param ProxyInterface $proxy
     *
     * @return string
     */
    protected function getResource($name, ProxyInterface $proxy)
    {
        return file_get_contents($proxy->getConfig()->getResourcePath() . $name);
    }
}