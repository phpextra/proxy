<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
 
namespace PHPExtra\Proxy\Cache;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The CacheStrategyInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface CacheStrategyInterface
{
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $cachedResponse
     *
     * @return bool
     */
    public function canUseResponseFromCache(RequestInterface $request, ResponseInterface $cachedResponse = null);

    /**
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     *
     * @return bool
     */
    public function canStoreResponseInCache(ResponseInterface $response, RequestInterface $request);
}