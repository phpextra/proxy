<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Voter;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The VoterInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface VoterInterface
{
    /**
     * Equals to 1000
     */
    const PRIORITY_HIGH = 1000;

    /**
     * Equals to 500
     */
    const PRIORITY_NORMAL = 500;

    /**
     * Equals to 0
     */
    const PRIORITY_LOW = 0;

    /**
     * Tell if given request can be served from cache if available
     *
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     *
     * @return bool
     */
    public function canUseResponseFromStorage(ResponseInterface $response, RequestInterface $request);

    /**
     * Tell if current response can be stored in cache
     *
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     *
     * @return bool
     */
    public function canStoreResponseInStorage(ResponseInterface $response, RequestInterface $request);

    /**
     * @return int
     */
    public function getPriority();
} 