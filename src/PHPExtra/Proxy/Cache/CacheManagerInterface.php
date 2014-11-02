<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Cache;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The CacheManagerInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface CacheManagerInterface
{
    /**
     * Set default lifetime for response if it does not contain the max-age directive
     *
     * @param int $lifetime
     *
     * @return $this
     */
    public function setLifetime($lifetime);

    /**
     * Return response or null if response could not be found
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function fetch(RequestInterface $request);

    /**
     * Save response for a given request
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function save(RequestInterface $request, ResponseInterface $response);
} 