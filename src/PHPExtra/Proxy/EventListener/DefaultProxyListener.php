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
use PHPExtra\Proxy\Proxy;
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

            $request = $event->getRequest();
            $event->getLogger()->debug(sprintf('%s', $request->getRequestUri()));

            if(!$this->firewall->isAllowed($request)){
                $event->setIsCancelled();
                $event->getLogger()->debug(sprintf('Request was cancelled as it was not allowed by a firewall'));
            }else{

                if ($request->hasHeader('X-Proxy-Marker')) {
                    $event->setIsCancelled();
                    $event->getLogger()->debug(sprintf('Proxy server made a call to itself that cannot be handled'));
                } else {
                    $event->getLogger()->debug(sprintf('Added %s header to request object', 'X-Proxy-Marker'));
                    $request->addHeader('X-Proxy-Marker', '1');
                }
            }
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
        if (!$event->isCancelled() && $event->hasResponse()) {
            $event->getLogger()->debug('Signing response with proxy name and version');
            $xPoweredBy = sprintf('%s v.%s', Proxy::NAME, Proxy::VERSION);
            $response = $event->getResponse();
            $response->addHeader('X-Powered-By', $xPoweredBy);
            $response->addHeader('X-Served-By', $xPoweredBy);
            $response->addHeader('X-Proxy-Version', Proxy::VERSION);
            $response->addHeader('X-Proxy-Url', Proxy::URL);
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

            if($event->getException() instanceof CancelledEventException){

                $response = new Response('Proxy cancelled your request', 403);
                $event->getLogger()->info('Proxy cancelled the request');

            }else{
                $response = new Response('Proxy was unable to complete your request due to an error', 500);
                $event->getLogger()->warning(sprintf('Proxy caught an exception: %s', get_class($event->getException())));
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