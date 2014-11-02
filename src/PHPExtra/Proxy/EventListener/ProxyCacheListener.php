<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\EventListener;

use PHPExtra\Proxy\Cache\CacheManagerInterface;
use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;

/**
 * Listen for incoming ProxyRequestEvent and decide whenever to store or read requests in and out from cache
 * This listener uses the VoterStackInterface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyCacheListener implements ProxyListenerInterface
{
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @param CacheManagerInterface $cacheManager
     */
    function __construct(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @priority normal
     *
     * @param ProxyRequestEvent $event
     */
    public function onProxyRequest(ProxyRequestEvent $event)
    {
        if(!$event->isCancelled() && !$event->hasResponse()){
            $response = $this->cacheManager->fetch($event->getRequest());

            if($response){
                $event->getLogger()->debug('Response was read from cache');
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
            $event->getLogger()->debug('Response was sent to cache');
            $this->cacheManager->save($event->getRequest(), $event->getResponse());
        }
    }

//    /**
//     * @param StorageInterface    $storage
//     * @param VoterStackInterface $voterStack
//     */
//    function __construct(StorageInterface $storage, VoterStackInterface $voterStack)
//    {
//        $this->storage = $storage;
//        $this->voterStack = $voterStack;
//    }
//
//    /**
//     * Process request before it will go through proxy engine
//     *
//     * @priority high
//     *
//     * @param ProxyRequestEvent $event
//     */
//    public function onProxyRequest(ProxyRequestEvent $event)
//    {
//        if (!$event->hasResponse() && !$event->isCancelled()) {
//            $request = $event->getRequest();
//            $response = $this->getStorage()->fetch($request);
//
//            if ($response) {
//
//                $canBeReadFromCache = $this->getVoterStack()->canUseResponseFromStorage($response, $request);
//
//                if (!$canBeReadFromCache) {
//                    $event->getLogger()->debug('Unable to read response from cache (not allowed by strategy)');
//                } else {
//
//                    $response = $this->getStorage()->fetch($request);
//
//                    $response->addHeader('X-Cache', 'HIT');
//                    $response->addHeader('X-Cache-Hits', 1);
//
//                    $event->setResponse($response);
//
//                    $event->getLogger()->debug('Response was read from cache');
//                }
//
//            } else {
//                $event->getLogger()->debug('Unable to read response from cache (not found in storage)');
//            }
//
//        }
//    }
//
//    /**
//     * Process response returned by proxy engine
//     *
//     * @priority low
//     *
//     * @param ProxyResponseEvent $event
//     */
//    public function onProxyResponse(ProxyResponseEvent $event)
//    {
//        if ($event->hasResponse() && !$event->isCancelled()) {
//            $request = $event->getRequest();
//            $response = $event->getResponse();
//
//            // fix headers if there was NO cache hit
//            if ($response->getHeader('X-Cache') != 'HIT') {
//                $response->addHeader('X-Cache', 'MISS');
//                $response->addHeader('X-Cache-Hits', 0);
//            }
//
//            if ($response->getHeader('X-Cache') == 'HIT') {
//                $event->getLogger()->debug('Response was NOT stored in cache (was from cache)');
//            } elseif (!$response->isSuccessful()) {
//                $event->getLogger()->debug('Response was NOT stored in cache (response was not successful)');
//            } elseif ($this->getVoterStack()->canStoreResponseInStorage($response, $request)) {
//                $this->getStorage()->save($response, $request);
//                $event->getLogger()->debug('Response was stored in cache');
//            } else {
//                $event->getLogger()->debug('Response was NOT stored in cache (not allowed by strategy)');
//            }
//        }
//    }
//
//    /**
//     * @return VoterStackInterface
//     */
//    private function getVoterStack()
//    {
//        return $this->voterStack;
//    }
//
//    /**
//     * @return StorageInterface
//     */
//    private function getStorage()
//    {
//        return $this->storage;
//    }
}