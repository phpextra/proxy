<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy;

/**
 * The ProxyFactoryInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ProxyFactoryInterface
{
    /**
     * @return ProxyInterface
     */
    public function create();
} 