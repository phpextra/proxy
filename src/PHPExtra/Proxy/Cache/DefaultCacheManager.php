<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
 
namespace PHPExtra\Proxy\Cache;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Storage\StorageInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The DefaultCacheManager class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DefaultCacheManager implements CacheManagerInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**s
     * @var int
     */
    private $lifetime = 3600;

    /**
     * @param StorageInterface $storage
     */
    function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        if(!$request->isNoCache() && $this->storage->has($request)){

            $response = $this->storage->fetch($request);

            if($response !== null && $response->isFresh()){
                $this->addCacheHitResponseHeaders($response);
                return $response;
            }else{
                $this->storage->delete($request);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response)
    {
        if(!$this->isResponseFromCache($response)){
            $this->addCacheMissResponseHeaders($response);
            $this->storage->save($request, $response, $this->lifetime);
        }

        return $this;
    }

    private function isResponseFromCache(ResponseInterface $response)
    {
        return $response->getHeader('x-cache') == 'HIT';
    }

    /**
     * @param ResponseInterface $response
     *
     * @return $this
     */
    private function addCacheHitResponseHeaders(ResponseInterface $response)
    {
        $response->addHeader('X-Cache', 'HIT');
        $response->addHeader('X-Cache-Hits', 1);

        return $this;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return $this
     */
    private function addCacheMissResponseHeaders(ResponseInterface $response)
    {
        $response->addHeader('X-Cache', 'MISS');
        $response->addHeader('X-Cache-Hits', 0);

        return $this;
    }
}