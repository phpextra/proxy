<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy;

use PHPExtra\Proxy\Http\RequestInterface;

/**
 * The FirewallInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface FirewallInterface
{
    /**
     * Allow given IP to use the proxy
     *
     * @param string $ip
     *
     * @return $this
     */
    public function allowIp($ip);

    /**
     * Allow to make proxy requests for given domain
     *
     * @param string $domain
     *
     * @return $this
     */
    public function allowDomain($domain);

    /**
     * Tell if current request is allowed depending on firewall rules
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function isAllowed(RequestInterface $request);
}