<?php

use PHPExtra\Proxy\Cache\CacheStrategyInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

class CacheStrategyMock implements CacheStrategyInterface
{
    /**
     * @var bool
     */
    private $canUseResponseFromCache = false;

    /**
     * @var bool
     */
    private $canStoreResponseInCache = false;

    /**
     * @param bool $value
     */
    public function setCanUseResponseFromCache($value)
    {
        $this->canUseResponseFromCache =  $value;
    }

    /**
     * @param bool $value
     */
    public function setCanStoreResponseInCache($value)
    {
        $this->canStoreResponseInCache = $value;
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $cachedResponse
     *
     * @return bool
     */
    public function canUseResponseFromCache(RequestInterface $request, ResponseInterface $cachedResponse = null)
    {
        return $this->canUseResponseFromCache;
    }

    /**
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     *
     * @return bool
     */
    public function canStoreResponseInCache(ResponseInterface $response, RequestInterface $request)
    {
        return $this->canStoreResponseInCache;
    }
}