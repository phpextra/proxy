<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Event;

use PHPExtra\Proxy\Http\ResponseInterface;
use PHPExtra\EventManager\Event\EventInterface;

/**
 * The ProxyEventInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ProxyEventInterface extends EventInterface
{
    /**
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * @return bool
     */
    public function hasResponse();
}