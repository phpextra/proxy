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
     * @var
     */
    private $stalledLifetime;

    /**
     * @param CacheStrategyInterface $cacheStrategy
     * @param StorageInterface       $storage
     * @param integer                $stalledLifetime
     */
    function __construct(CacheStrategyInterface $cacheStrategy, StorageInterface $storage, $stalledLifetime = 3600)
    {
        $this->cacheStrategy = $cacheStrategy;
        $this->storage = $storage;
        $this->stalledLifetime = $stalledLifetime;
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
                $event->getLogger()->debug('Response was read from cache');
                $response->addHeader('X-Cache', 'HIT');
                $response->addHeader('X-Cache-Hits', 1);
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
        if(!$event->isCancelled() && $event->hasResponse()) {
            $response = $event->getResponse();
            $request = $event->getRequest();
            $logger = $event->getLogger();

            if($response->isServerError() && $this->stalledLifetime != 0) {
                $logger->warning(sprintf('Server returned error %d', $response->getStatusCode()));
                $stalledResponse = $this->storage->fetch($request);

                if($stalledResponse !== null) {
                    $logger->warning('Returning stalled response');
                    $event->setResponse($stalledResponse);
                } else {
                    $logger->warning('Stalled response not available, returning error response!');
                }
            }

            if(!$response->hasHeaderWithValue('X-Cache', 'HIT')){
                $response->addHeader('X-Cache', 'MISS');
                $response->addHeader('X-Cache-Hits', 0);
            }

            if($this->cacheStrategy->canStoreResponseInCache($response, $event->getRequest())){
                $this->storage->save($request, $response, $response->getTtl() + $this->stalledLifetime);
                $logger->debug('Response was stored in cache');
            }
        }
    }
}