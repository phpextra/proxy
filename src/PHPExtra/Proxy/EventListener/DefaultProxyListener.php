<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\Event\ProxyExceptionEvent;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\Exception\CancelledEventException;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Http\Response;
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
     * @priority highest
     *
     * @param ProxyRequestEvent $event
     */
    public function onProxyRequest(ProxyRequestEvent $event)
    {
        if (!$event->isCancelled()) {

            $response = null;
            $request = $event->getRequest();
            $event->getLogger()->debug(sprintf('%s', $request->getRequestUri()));
            $config = $event->getProxy()->getConfig();

            $proxyUniqueHeaderName = 'PROXY-ID';
            $proxyUniqueHeaderValue = md5($config->getSecret() . $request->getFingerprint());

            if (!$response && $request->hasHeaderWithValue($proxyUniqueHeaderName, $proxyUniqueHeaderValue)) {
                $event->setIsCancelled();
                $event->getLogger()->warning(sprintf('Proxy server made a call to itself that cannot be handled, request will be cancelled'));
            } else {
                $event->getLogger()->debug(sprintf('Added %s header to request object', $proxyUniqueHeaderName));
                $request->addHeader($proxyUniqueHeaderName, $proxyUniqueHeaderValue);
            }

            if(!$response && !$this->firewall->isAllowed($request)){
                $event->setIsCancelled();
                $event->getLogger()->debug(sprintf('Request was cancelled as it was not allowed by a firewall'));
            }

            if(in_array($request->getHost(), $config->getHostsOnPort($request->getPort()))){
                // display proxy welcome page as it is a direct hit
                $response = new Response(sprintf('<html><h1>It works!</h1><body><p>Find out more about me on <a href="https://github.com/phpextra/proxy">GitHub</a>.</p></body></html>'), 200);
            }

            $event->setResponse($response);
        }
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

                $message = '<h1>Forbidden</h1><p>Proxy cancelled your request.</p>';
                $response = new Response($message, 403);

            }else{
                $response = new Response('Proxy was unable to complete your request due to an unknown error', 500);
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
                $event->getRequest()->getRequestUri(),
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
            $event->getLogger()->log(
                LogLevel::WARNING,
                'Proxy failed to produce valid response object; it also failed to handle the error that occurred'
            );
        }
    }
}