<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\Cache\CacheStrategyInterface;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\Storage\StorageInterface;

/**
 * Listen for incoming ProxyRequestEvent and decide whenever to store or read requests in and out from cache
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyCacheListener implements ProxyListenerInterface
{
    /**
     * @var CacheStrategyInterface
     */
    private $cacheStrategy;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param CacheStrategyInterface $cacheStrategy
     * @param StorageInterface       $storage
     */
    function __construct(CacheStrategyInterface $cacheStrategy, StorageInterface $storage)
    {
        $this->cacheStrategy = $cacheStrategy;
        $this->storage = $storage;
    }

    /**
     *
     * @priority normal
     * @param ProxyRequestEvent $event
     *
     * @throws \Exception
     */
    public function onProxyRequest(ProxyRequestEvent $event)
    {
        if(!$event->isCancelled() && !$event->hasResponse()){

            $response = $this->storage->fetch($event->getRequest());
            $request = $event->getRequest();

            if($this->cacheStrategy->canUseResponseFromCache($request, $response)){

                /**
                 * The "Date" header field represents the date and time at which the message was originated
                 * http://tools.ietf.org/html/rfc7231#section-7.1.1.2
                 */

                $event->getLogger()->debug('Response was read from cache');
                $response->addHeader('X-Cache', 'HIT');
                $response->addHeader('X-Cache-Hits', 1);
                $now = new \DateTime('now');

                $oldAge = $response->getHeader('Age', 0);
                $oldAge = $oldAge[0];

                $response->setHeader('Age', $oldAge + ($now->getTimestamp() - $response->getDate()->getTimestamp()));
                $event->setResponse($response);
            }
        }
    }

    /**
     * @priority normal
     *
     * @param ProxyResponseEvent $event
     */
    public function onProxyResponse(ProxyResponseEvent $event)
    {
        if(!$event->isCancelled() && $event->hasResponse()){

            $response = $event->getResponse();
            $request = $event->getRequest();

            if(!$response->hasHeaderWithValue('X-Cache', 'HIT')){
                $response->addHeader('X-Cache', 'MISS');
                $response->addHeader('X-Cache-Hits', 0);
            }

            $oldVia = $response->getHeader('Via');
            $oldVia[] = $event->getProxy()->getConfig()->getProxyName();

            $response->setHeader('Via', $oldVia);

            if($this->cacheStrategy->canStoreResponseInCache($response, $event->getRequest())){
                $this->storage->save($request, $response, $response->getTtl());
                $event->getLogger()->debug('Response was stored in cache');
            }
        }
    }
}