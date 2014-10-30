<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy;

/**
 * The FirewallInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface FirewallInterface
{
    public function allowClientIp($ip);

    public function denyClientIp($ip);

    public function isClientIpAllowed($ip);

    public function isUrlAllowed($url);
}