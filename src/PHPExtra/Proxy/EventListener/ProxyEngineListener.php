<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\Engine\ProxyEngineInterface;
use PHPExtra\Proxy\Event\ProxyEngineEvent;

/**
 * Trigger proxy engine during ProxyRequestEvent
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyEngineListener implements ProxyListenerInterface
{
    /**
     * @var ProxyEngineInterface
     */
    private $engine;

    /**
     * @param ProxyEngineInterface $engine
     */
    function __construct(ProxyEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Run proxy engine
     *
     * @priority normal
     *
     * @param ProxyEngineEvent $event
     */
    public function onProxyEngineEvent(ProxyEngineEvent $event)
    {
        if(!$event->isCancelled() && !$event->hasResponse()){
            $response = $this->engine->handle($event->getRequest());
            $event->setResponse($response);
        }
    }
}