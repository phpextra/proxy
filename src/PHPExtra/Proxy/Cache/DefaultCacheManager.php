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

    /**
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



            if($response === null){
                $this->storage->delete($request);
            }elseif($this->isResponseFreshEnoughForTheClient($request, $response)){
                $this->addCacheHitResponseHeaders($response);
                return $response;
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

    /**
     * Tell if given response with its max age is fresh enough for the client
     * that can also request a response of some age
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isResponseFreshEnoughForTheClient(RequestInterface $request, ResponseInterface $response)
    {

        $compare = array();
        if($request->getMaxAge() !== null){
            $compare[] = $request->getMaxAge();
        }

        if($response->getMaxAge() !== null){
            $compare[] = $response->getMaxAge();
        }

        if(count($compare) == 0 || ($maxAge = min($compare)) <= 0){
            return false;
        }

        $expirationDate = $response->getDate()->add(new \DateInterval(sprintf('PT%sS', $maxAge)));
        $now = new \DateTime('now', $response->getDate()->getTimezone());

        return $expirationDate >= $now;
    }

    /**
     * Tell if current response comes from cache
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isResponseFromCache(ResponseInterface $response)
    {
        return $response->hasHeaderWithValue('X-Cache', 'HIT');
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