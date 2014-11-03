<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\Event\ProxyAdapterEvent;

/**
 * Trigger proxy adapter during ProxyRequestEvent
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyAdapterListener implements ProxyListenerInterface
{
    /**
     * @var ProxyAdapterInterface
     */
    private $adapter;

    /**
     * @param ProxyAdapterInterface $adapter
     */
    function __construct(ProxyAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Run proxy adapter
     *
     * @priority normal
     *
     * @param ProxyAdapterEvent $event
     */
    public function onProxyAdapterEvent(ProxyAdapterEvent $event)
    {
        if(!$event->isCancelled() && !$event->hasResponse()){
            $response = $this->adapter->handle($event->getRequest());
            $event->setResponse($response);
        }
    }
}