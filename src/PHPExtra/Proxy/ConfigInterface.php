<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy;

/**
 * The Proxy config class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ConfigInterface
{
    /**
     * @return string
     */
    public function getProxyName();

    /**
     * Get secret used to salt various proxy params
     * It must be unique
     *
     * @return string
     */
    public function getSecret();

    /**
     * @return string
     */
    public function getProxyVersion();

    /**
     * Get all hosts on a given port
     *
     * @param int $port
     *
     * @return array
     */
    public function getHostsOnPort($port = 80);

    /**
     * Return an array of all used hosts (without port number)
     *
     * @return array
     */
    public function getAllHosts();

    /**
     * Check if stalling responses on error is enabled.
     *
     * @return boolean
     */
    public function isStallingResponsesEnabled();
}