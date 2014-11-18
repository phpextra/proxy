<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy;

use PHPExtra\Proxy\Adapter\ProxyAdapterAwareInterface;
use PHPExtra\Proxy\Adapter\ProxyAdapterInterface;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The ProxyInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ProxyInterface extends ProxyAdapterAwareInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request);

    /**
     * Add listener
     *
     * @param ProxyListenerInterface $listener
     * @param int                    $priority
     *
     * @return $this
     */
    public function addListener(ProxyListenerInterface $listener, $priority = null);
}