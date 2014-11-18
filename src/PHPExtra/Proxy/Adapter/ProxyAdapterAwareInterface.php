<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Adapter;

/**
 * Process given request and return appropriate response
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ProxyAdapterAwareInterface
{
    /**
     * Set adapter that will be used to make http requests
     *
     * @param ProxyAdapterInterface $adapter
     *
     * @return $this
     */
    public function setAdapter(ProxyAdapterInterface $adapter);
} 